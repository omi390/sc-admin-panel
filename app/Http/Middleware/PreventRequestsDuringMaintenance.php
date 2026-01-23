<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Support\Facades\Artisan;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [];

}
