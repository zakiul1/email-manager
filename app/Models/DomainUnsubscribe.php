<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DomainUnsubscribe extends Model
{
    /**
     * type values:
     * - domain     => exact domain match (gmail.com)
     * - extension  => suffix match (.bd, .com.bd)
     * - user       => local-part match (pk_d, pk.dutta) [input can be pk_d@]
     */
    protected $fillable = [
        'type',
        'value',
        'reason',
        'user_id',
    ];

    /**
     * Normalize before save:
     * - lowercase
     * - remove spaces
     * - remove leading @
     * - domain: remove leading dot (.) and trailing @
     * - extension: enforce leading dot (.)
     * - user: keep only local-part (remove trailing @ if provided)
     */
    protected static function booted(): void
    {
        static::saving(function (self $m) {
            $m->type = self::normalizeType($m->type);

            $value = mb_strtolower(trim((string) $m->value));
            $value = preg_replace('/\s+/', '', $value) ?? $value;

            // common cleanup
            $value = ltrim($value, '@');

            if ($m->type === 'domain') {
                // allow "gmail.com" or "@gmail.com" or ".gmail.com"
                $value = ltrim($value, '.');
                // if someone typed gmail.com@, remove trailing @
                $value = rtrim($value, '@');
            }

            if ($m->type === 'extension') {
                // allow ".bd" or "bd" or "@bd" or " .com.bd "
                $value = rtrim($value, '@');
                $value = ltrim($value, '.');
                if ($value !== '') {
                    $value = '.' . $value;
                }
            }

            if ($m->type === 'user') {
                // allow "pk_d" or "pk_d@" or "@pk_d@" etc.
                $value = rtrim($value, '@');

                // if someone pasted a full email, keep only local-part (before @)
                if (str_contains($value, '@')) {
                    $value = explode('@', $value, 2)[0];
                }

                // also avoid accidental leading dots
                $value = ltrim($value, '.');
            }

            $m->value = $value;
        });
    }

    public static function normalizeType(?string $type): string
    {
        $t = mb_strtolower(trim((string) $type));
        return in_array($t, ['domain', 'extension', 'user'], true) ? $t : 'domain';
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

    public function scopeUsers(Builder $q): Builder
    {
        return $q->where('type', 'user');
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

    /**
     * Helper: check if a local-part (username) is blocked
     * - type=user, value=local_part
     * - input examples: "pk_d" or "pk_d@" or full email "pk_d@gmail.com"
     */
    public static function isBlockedUser(string $localPartOrEmail): bool
    {
        $v = mb_strtolower(trim($localPartOrEmail));
        $v = preg_replace('/\s+/', '', $v) ?? $v;

        if ($v === '') {
            return false;
        }

        $v = ltrim($v, '@');
        $v = rtrim($v, '@');

        // if full email provided, keep only local part
        if (str_contains($v, '@')) {
            $v = explode('@', $v, 2)[0];
        }

        if ($v === '') {
            return false;
        }

        return self::query()
            ->where('type', 'user')
            ->where('value', $v)
            ->exists();
    }
}