<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppSetting extends Model
{
    use SoftDeletes;

    protected $table = 'app_settings';

    protected $fillable = [
        'name',
        'type',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get SST configuration
     */
    public static function getSstConfiguration()
    {
        $setting = self::where('type', 'sst_configuration')->first();
        
        if (!$setting) {
            // Return default SST configuration
            return [
                'status' => 0, // 0 = disabled by default
                'sst_percent' => 6.0 // 6% default
            ];
        }

        return $setting->value ?? [
            'status' => 0,
            'sst_percent' => 6.0
        ];
    }

    /**
     * Update SST configuration
     */
    public static function updateSstConfiguration($data)
    {
        $setting = self::where('type', 'sst_configuration')->first();
        
        if (!$setting) {
            $setting = new self();
            $setting->name = 'SST Configuration';
            $setting->type = 'sst_configuration';
        }

        $setting->value = [
            'status' => $data['status'] ?? 0,
            'sst_percent' => $data['sst_percent'] ?? 6.0
        ];

        $setting->save();
        
        return $setting;
    }
}
