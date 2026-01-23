<li class="nav-item">
    <a class="nav-link {{$webPage=='google_map'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=google_map">
        {{translate('Map Api')}}
    </a>
</li>
<li class="nav-item">
    <a
        class="nav-link {{$webPage=='push_notification'?'active':''}}"
        href="{{url('admin/configuration/get-third-party-config')}}?web_page=push_notification">
        {{translate('firebase_notification_setup')}}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{$webPage=='recaptcha'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=recaptcha">
        {{translate('recaptcha')}}
    </a>
</li>
<li class="nav-item">
    <a class="nav-link {{$webPage=='apple_login'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=apple_login">
        {{translate('apple_login')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{$webPage=='email_config' || $webPage == 'test_mail' ?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=email_config">
        {{translate('email_config')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{$webPage=='sms_config'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=sms_config">
        {{translate('Sms Config')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ $webPage == 'payment_config' ? 'active' : '' }}" href="{{ request()->is('admin/configuration/offline-payment/*') ? url('admin/configuration/offline-payment/list?web_page=payment_config&type=offline_payment') : url('admin/configuration/get-third-party-config?web_page=payment_config&type=digital_payment') }}">
        {{ translate('Payment Config') }}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{$webPage=='storage_connection'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=storage_connection">
        {{translate('Storage Connection')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{$webPage=='app_settings'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=app_settings">
        {{translate('App Settings')}}
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{$webPage=='firebase_otp_verification'?'active':''}}"
       href="{{url('admin/configuration/get-third-party-config')}}?web_page=firebase_otp_verification">
        {{translate('firebase_auth_verification')}}
    </a>
</li>
