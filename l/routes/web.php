<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('lang/{locale}', [LandingController::class, 'lang'])->name('lang');
Route::get('/', [LandingController::class, 'home'])->name('home');
Route::get('page/about-us', [LandingController::class, 'aboutUs'])->name('page.about-us');
Route::get('page/privacy-policy', [LandingController::class, 'privacyPolicy'])->name('page.privacy-policy');
Route::get('page/terms-and-conditions', [LandingController::class, 'termsAndConditions'])->name('page.terms-and-conditions');
Route::get('page/contact-us', [LandingController::class, 'contactUs'])->name('page.contact-us');
Route::get('page/cancellation-policy', [LandingController::class, 'cancellationPolicy'])->name('page.cancellation-policy');
Route::get('page/refund-policy', [LandingController::class, 'refundPolicy'])->name('page.refund-policy');
Route::get('maintenance-mode', [LandingController::class, 'maintenanceMode'])->name('maintenance-mode');

Route::fallback(function () {
    return redirect('admin/auth/login');
});

Route::get('test', function (){

});



