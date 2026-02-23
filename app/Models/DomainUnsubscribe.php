<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DomainUnsubscribe extends Model
{
    protected $fillable = [
        'type',   // domain | extension
        'value',  // gmail.com | .bd | .com.bd
        'reason',
        'user_id',
    ];

    /**
     * Normalize before save:
     * - lowercase
     * - remove spaces
     * - remove leading @ for domains
     * - ensure extension starts with dot (.)
     */
    protected static function booted(): void
    {
        static::saving(function (self $m) {
            $m->type = self::normalizeType($m->type);

            $value = mb_strtolower(trim((string) $m->value));
            $value = preg_replace('/\s+/', '', $value) ?? $value;

            // domain: allow "gmail.com" or "@gmail.com"
            if ($m->type === 'domain') {
                $value = ltrim($value, '@');
                $value = ltrim($value, '.'); // avoid ".gmail.com" mistakes
            }

            // extension: enforce leading dot
            if ($m->type === 'extension') {
                $value = ltrim($value, '@'); // just in case
                if ($value !== '' && $value[0] !== '.') {
                    $value = '.' . $value;
                }
            }

            $m->value = $value;
        });
    }

    public static function normalizeType(?string $type): string
    {
        $t = mb_strtolower(trim((string) $type));
        return in_array($t, ['domain', 'extension'], true) ? $t : 'domain';
    }

    /**
     * Convenience scopes
     */
    public function scopeDomains(Builder $q): Builder
    {
        return $q->where('type', 'domain');
    }

    public function scopeExtensions(Builder $q): Builder
    {
        return $q->where('type', 'extension');
    }

    /**
     * Helper: check if a domain is blocked
     * - exact domain match (type=domain, value=domain)
     * - extension match (type=extension, value=".bd" matches "something.bd")
     * - extension match (type=extension, value=".com.bd" matches "x.com.bd")
     */
    public static function isBlockedDomain(string $domain): bool
    {
        $d = mb_strtolower(trim($domain));
        $d = ltrim($d, '@');

        if ($d === '') {
            return false;
        }

        // 1) exact domain match
        $exact = self::query()
            ->where('type', 'domain')
            ->where('value', $d)
            ->exists();

        if ($exact) {
            return true;
        }

        // 2) extension match: check suffixes
        // Example: domain = "abc.com.bd" -> suffixes: ".bd", ".com.bd"
        $parts = explode('.', $d);
        if (count($parts) < 2) {
            return false;
        }

        $suffixes = [];
        for ($i = count($parts) - 1; $i >= 1; $i--) {
            $suffixes[] = '.' . implode('.', array_slice($parts, $i));
        }

        return self::query()
            ->where('type', 'extension')
            ->whereIn('value', $suffixes)
            ->exists();
    }
}