<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EmailServiceProvider extends Model
{
    // Directory where Email Service Providers live
    const ESP_DIR = 'Interfaces/EmailServiceProvider';

    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'provider_type',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function emailDripCampaigns()
    {
        return $this->hasMany('App\Models\EmailDripCampaign');
    }

    public static function providerTypes()
    {
        // Look in the directory for provider interfaces
        $files = collect(File::allFiles(app_path(self::ESP_DIR)));

        $provider_types = [];

        foreach ($files as $file) {
            $provider_type = Str::snake(substr($file->getFilename(), 0, -4));
            $class = 'App\\' . str_replace('/', '\\', self::ESP_DIR) . '\\' .
                Str::studly($provider_type);
            $provider_types[$provider_type] = $class::description();
        };

        return $provider_types;
    }

    public function providerClassName()
    {
        // full path the class so we don't have to import it
        return 'App\\' . str_replace('/', '\\', self::ESP_DIR) . '\\' .
            Str::studly($this->provider_type);
    }
}
