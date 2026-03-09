<?php

namespace App\Services\SendPortal;

use App\Models\SendPortal\Setting;

class SettingsService
{
    public function getGroup(string $group): array
    {
        return Setting::query()
            ->where('group', $group)
            ->get()
            ->mapWithKeys(function (Setting $setting) {
                return [$setting->key => $setting->value_json];
            })
            ->toArray();
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $setting?->value_json ?? $default;
    }

    public function put(string $group, string $key, mixed $value, bool $isEncrypted = false): Setting
    {
        return Setting::query()->updateOrCreate(
            [
                'group' => $group,
                'key' => $key,
            ],
            [
                'value_json' => $value,
                'is_encrypted' => $isEncrypted,
            ]
        );
    }

    public function putMany(string $group, array $values, array $encryptedKeys = []): void
    {
        foreach ($values as $key => $value) {
            $this->put(
                $group,
                (string) $key,
                $value,
                in_array($key, $encryptedKeys, true)
            );
        }
    }

    public function allStructured(): array
    {
        return Setting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn ($rows) => $rows->mapWithKeys(fn ($row) => [$row->key => $row->value_json])->toArray())
            ->toArray();
    }
}