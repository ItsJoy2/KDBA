<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    /**
     * Get a setting value.
     */
    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {
        $setting = static::query()
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'boolean') {
            return filter_var(
                $setting->value,
                FILTER_VALIDATE_BOOLEAN
            );
        }

        if ($setting->type === 'integer') {
            return (int) $setting->value;
        }

        if ($setting->type === 'json') {
            return json_decode($setting->value, true);
        }

        return $setting->value;
    }

    /**
     * Create or update a setting without affecting other settings.
     */
    public static function setValue(
        string $key,
        mixed $value,
        string $type = 'text',
        string $group = 'general'
    ): self {
        return static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value)
                    ? json_encode($value)
                    : (string) $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }
}
