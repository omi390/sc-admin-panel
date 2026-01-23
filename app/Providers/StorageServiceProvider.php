<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $s3Credentials = business_config('s3_storage_credentials', 'storage_settings');
        if ($s3Credentials !== null) {
            try {
                $liveValues = json_decode($s3Credentials->live_values, true);

                $s3Credentials = [
                    'driver' => 's3',
                    'key' => $liveValues['key'],
                    'secret' => $liveValues['secret'],
                    'region' => $liveValues['region'],
                    'bucket' => $liveValues['bucket'],
                    'url' => $liveValues['url'],
                    'endpoint' => $liveValues['endpoint'],
                ];

                Config::set('filesystems.disks.s3', $s3Credentials);
            }catch(\Exception $exception){

            }

        }
    }
}
