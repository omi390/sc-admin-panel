<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Reward Management (Admin)
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.reward-point.',
    'namespace' => 'Web\Admin',
    'middleware' => ['admin'],
], function () {
    Route::group(['prefix' => 'reward-point', 'as' => 'config.'], function () {
        Route::get('config/list', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'index'])->name('list');
        Route::get('config/create', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'create'])->name('create');
        Route::post('config/store', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'store'])->name('store');
        Route::get('config/edit/{id}', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'edit'])->name('edit');
        Route::put('config/update/{id}', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'update'])->name('update');
        Route::delete('config/delete/{id}', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'destroy'])->name('delete');
    });

    Route::get('reward-point/usage', [\Modules\RewardModule\Http\Controllers\Web\Admin\RewardPointConfigController::class, 'usage'])->name('usage');
});
