<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GenerateAdminRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:admin-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate admin formatted routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::getRoutes();
        $adminRoutes = collect($routes->getRoutesByMethod()['GET'])->filter(function ($route) {
            return Str::startsWith($route->uri(), 'admin');
        });

        $excludeTerms = [
            'ajax', 'download', 'export', 'edit', 'login', 'logout', 'invoice', 'verify', 'otp',
            'backup-database-backup', 'child', 'channel', 'third', 'party', 'update-default-status', 'check', 'childes',
            'referenced-channel-list', 'channel-list', 'test-send-email', 'get-updated-data', 'update-status', 'provider-info',
            'update-dashboard-earning-graph', 'set-message-setting', 'available-provider'
        ];

        $formattedRoutes = [];
        foreach ($adminRoutes as $route) {
            $uri = $route->uri();
            $exclude = collect($excludeTerms)->contains(function ($term) use ($uri) {
                return Str::contains($uri, $term);
            });

            if (!$exclude) {
                $hasParameters = preg_match('/\{(.*?)\}/', $uri);
                if (!$hasParameters) {
                    $actualRouteName = $route->getName();
                    $routeName = ucwords(str_replace(['.', '_'], ' ', Str::afterLast($actualRouteName, '.')));
                    $keywords = preg_replace('/^admin[._]/', '', $actualRouteName);
                    $keywords = ucwords(str_replace(['.', '_'], ' ', $keywords));

                    $formattedRoutes[] = [
                        'routeName' => $routeName,
                        'URI' => $uri,
                        'keywords' => $keywords,
                        'isModified' => false
                    ];
                }
            }
        }

        $jsonFilePath = public_path('admin_formatted_routes.json');

        if (file_exists($jsonFilePath)) {
            $fileContents = file_get_contents($jsonFilePath);
            $existingRoutes = json_decode($fileContents, true) ?? [];

            $newRoutes = array_filter($formattedRoutes, function ($newRoute) use ($existingRoutes) {
                foreach ($existingRoutes as $existingRoute) {
                    if ($existingRoute['URI'] === $newRoute['URI']) {
                        return false;
                    }
                }
                return true;
            });

            if (!empty($newRoutes)) {
                $updatedRoutes = array_merge($existingRoutes, $newRoutes);
                file_put_contents($jsonFilePath, json_encode($updatedRoutes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            file_put_contents($jsonFilePath, json_encode($formattedRoutes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return 0;
    }
}
