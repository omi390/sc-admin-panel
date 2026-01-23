<?php

use Illuminate\Support\Facades\Route;
use Modules\CartModule\Http\Controllers\Api\V1\Customer\CartController;


Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer'], function () {
    Route::group(['prefix' => 'cart', 'as' => 'cart.',], function () {
        Route::post('add', [CartController::class, 'addToCart']);
        Route::get('list', [CartController::class, 'list']);
        Route::put('update-quantity/{id}', [CartController::class, 'updateQty']);
        Route::put('update/provider', [CartController::class, 'updateProvider']);
        Route::delete('remove/{id}', [CartController::class, 'remove']);
        Route::delete('data/empty', [CartController::class, 'emptyCart']);
    });

    Route::group(['prefix' => 'rebook', 'as' => 'rebook.',], function () {
        Route::post('cart-add', [CartController::class, 'rebookAddToCart']);
    });
});

