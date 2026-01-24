<?php

namespace Modules\CustomerModule\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function aboutUs(): Renderable
    {
        $page_data = business_config('about_us', 'pages_setup');
        return view('customermodule::index', compact('page_data'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function privacyPolicy(): Renderable
    {
        $page_data = business_config('privacy_policy', 'pages_setup');
        return view('customermodule::index', compact('page_data'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function termsAndConditions(): Renderable
    {
        $page_data = business_config('terms_and_conditions', 'pages_setup');
        return view('customermodule::index', compact('page_data'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function refundPolicy(): Renderable
    {
        $page_data = business_config('refund_policy', 'pages_setup');
        return view('customermodule::index', compact('page_data'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function returnPolicy(): Renderable
    {
        $page_data = business_config('return_policy', 'pages_setup');
        return view('customermodule::index', compact('page_data'));
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function cancellationPolicy(): Renderable
    {
        $page_data = business_config('cancellation_policy', 'pages_setup');
        return view('customermodule::index', compact('page_data'));
    }
}
