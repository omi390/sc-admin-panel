<?php

namespace Modules\AddonModule\Traits;

trait AddonHelper
{
    public function get_addons(): array
    {
        $dir = base_path('Modules');
        $directories = $this->getDirectories($dir);
        $addons = [];

        foreach ($directories as $directory) {
            $subDirectories = $this->getDirectories($dir . '/' . $directory);
            if (in_array('Addon', $subDirectories)) {
                $addons[] = $dir . '/' . $directory;
            }
        }

        $array = [];
        foreach ($addons as $item) {
            if (file_exists($item . '/Addon/info.php')) {
                $fullData = include($item . '/Addon/info.php');
                $array[] = [
                    'addon_name' => $fullData['name'] ?? null,
                    'software_id' => $fullData['software_id'] ?? null,
                    'is_published' => $fullData['is_published'] ?? false,
                ];
            }
        }

        return $array;
    }

    public function get_addon_admin_routes(): array
    {
        $dir = base_path('Modules');
        $directories = $this->getDirectories($dir);
        $addons = [];

        foreach ($directories as $directory) {
            $subDirectories = $this->getDirectories($dir . '/' . $directory);
            if (in_array('Addon', $subDirectories)) {
                $addons[] = $dir . '/' . $directory;
            }
        }

        $fullData = [];
        foreach ($addons as $item) {
            $infoFile = $item . '/Addon/info.php';
            $routesFile = $item . '/Addon/admin_routes.php';

            if (file_exists($infoFile) && file_exists($routesFile)) {
                $info = include($infoFile);
                if (!empty($info['is_published'])) {
                    $fullData[] = include($routesFile);
                }
            }
        }

        return $fullData;
    }

    public function get_payment_publish_status(): array
    {
        $dir = base_path('Modules');
        $directories = $this->getDirectories($dir);
        $addons = [];

        foreach ($directories as $directory) {
            $subDirectories = $this->getDirectories($dir . '/' . $directory);
            if ($directory == 'Gateways' && in_array('Addon', $subDirectories)) {
                $addons[] = $dir . '/' . $directory;
            }
        }

        $array = [];
        foreach ($addons as $item) {
            $infoFile = $item . '/Addon/info.php';
            if (file_exists($infoFile)) {
                $fullData = include($infoFile);
                $array[] = [
                    'is_published' => $fullData['is_published'] ?? false,
                ];
            }
        }

        return $array;
    }

    function getDirectories(string $path): array
    {
        $directories = [];
        if (!is_dir($path)) return []; // prevent scandir errors
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.') continue;
            if (is_dir($path . '/' . $item)) $directories[] = $item;
        }
        return $directories;
    }
}

