<?php


use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;

if (!function_exists('format_coordinates')) {
    function format_coordinates($object): array
    {
        $data = [];
        foreach ($object as $coordinate) {
            $data[] = (object)['lat' => $coordinate[1], 'lng' => $coordinate[0]];
        }
        return $data;
    }
}
if (!function_exists('formatCoordinates')) {
    function formatCoordinates($coordinates): array
    {
        $object = json_decode($coordinates[0]->toJson(),true)['coordinates'];
        $data = [];
        foreach ($object as $coordinate) {
            $data[] = (object)['latitude' => $coordinate[0], 'longitude' => $coordinate[1]];
        }
        return $data;
    }
}
