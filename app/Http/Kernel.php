<?php

namespace App\Http;

use App\Http\Middleware\ApiHitLimitMiddleware;
use App\Http\Middleware\LocalizationMiddleware;
use App\Http\Middleware\ZoneAdder;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Modules\AdminModule\Http\Middleware\AdminMiddleware;
use Modules\BidModule\Http\Middleware\EnsureBiddingIsActive;
use Modules\ProviderManagement\Http\Middleware\ProviderMiddleware;
use Modules\UserManagement\Http\Middleware\AdminModulePermission;
use Modules\UserManagement\Http\Middleware\DetectUser;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        ZoneAdder::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\Localization::class,
            \App\Http\Middleware\SubscriptionModalMiddleware::class,
            \App\Http\Middleware\MaintenanceMode::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            LocalizationMiddleware::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'mpc' => AdminModulePermission::class,
        'hitLimiter' => ApiHitLimitMiddleware::class,
        'detectUser' => DetectUser::class,
        'admin' => AdminMiddleware::class,
        'provider' => ProviderMiddleware::class,
        'ensureBiddingIsActive' => EnsureBiddingIsActive::class,
        'localization' => \App\Http\Middleware\LocalizationMiddleware::class,
        'subscription' => \App\Http\Middleware\Subscription::class,
    ];
}
