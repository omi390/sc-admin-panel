<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>

    <?php
    $favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/placeholder.png')
    ?>
    <link rel="shortcut icon" href="{{ $favIcon }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet"/>

    <link href="{{asset('public/assets/provider-module')}}/css/material-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css"/>

    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/intl-tel-input/css/intlTelInput.min.css')}}">
    
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/style.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/toastr.css">
    @stack('css_or_js')
</head>

<body>

<div class="preloader"></div>

<?php
$logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/placeholder.png');
?>

@yield('content')

<script src="{{asset('public/assets/provider-module')}}/js/jquery-3.6.0.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/main.js"></script>

<script src="{{asset('public/assets/provider-module')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/provider-module')}}/js/toastr.js"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
<script src="{{ asset('public/assets/admin-module/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/js/country-picker-init.js') }}"></script>
@stack('script')
</body>
</html>
