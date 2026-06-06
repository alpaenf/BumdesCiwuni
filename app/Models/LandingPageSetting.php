<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageSetting extends Model
{
    protected $table = 'landing_page_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getByKey(string $key, string $default = ''): string
    {
        return self::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setByKey(string $key, ?string $value): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
