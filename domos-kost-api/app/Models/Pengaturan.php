<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'deskripsi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get setting value with proper type casting
     */
    public function getTypedValueAttribute()
    {
        switch ($this->setting_type) {
            case 'boolean':
                return (bool) $this->setting_value;
            case 'number':
                return (float) $this->setting_value;
            case 'json':
                return json_decode($this->setting_value, true);
            default:
                return $this->setting_value;
        }
    }

    /**
     * Get setting by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $type = 'text', string $deskripsi = null): void
    {
        $setting = static::firstOrNew(['setting_key' => $key]);
        
        // Convert value to string for storage
        if ($type === 'boolean') {
            $value = $value ? '1' : '0';
        } elseif ($type === 'json') {
            $value = json_encode($value);
        }
        
        $setting->setting_value = $value;
        $setting->setting_type = $type;
        
        if ($deskripsi && !$setting->exists) {
            $setting->deskripsi = $deskripsi;
        }
        
        $setting->save();
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAll(): array
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->setting_key => $setting->typed_value];
        })->toArray();
    }
}
