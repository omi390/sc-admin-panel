<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{translate('Subscription Plan Renewed!')}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">

        @media screen {
            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 400;
                src: local('Source Sans Pro Regular'), local('SourceSansPro-Regular'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format('woff');
            }

            @font-face {
                font-family: 'Source Sans Pro';
                font-style: normal;
                font-weight: 700;
                src: local('Source Sans Pro Bold'), local('SourceSansPro-Bold'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format('woff');
            }
        }

        body,
        table,
        td,
        a {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-rspace: 0pt;
            mso-table-lspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        a[x-apple-data-detectors] {
            font-family: inherit !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
            color: inherit !important;
            text-decoration: none !important;
        }

        div[style*="margin: 16px 0;"] {
            margin: 0 !important;
        }

        body {
            width: 100% !important;
            height: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        table {
            border-collapse: collapse !important;
        }

        a {
            color: #1a82e2;
        }

        .list-inline {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .list-inline li {
            display: inline-block;
        }

        .bg-white {
            background-color: #fff !important;
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        p, h3, h5 {
            margin-top: 0;
            margin-bottom: 16px;
        }

        .btn {
            font-size: 14px;
            font-weight: 600;
            text-transform: capitalize;
            line-height: 1;
            padding: 0.75rem 1.625rem;
            outline: none;
            white-space: nowrap;
            border: none;
            background-color: #1455AC;
            color: #fff;
        }

        hr {
            margin-block-start: 2rem;
            margin-block-end: 2rem;
        }

        .top-wrap-box > *:not(:last-child) {
            margin-top: 10px;
        }

        .list-gap {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .list-gap-2 > *,
        .list-gap > * {
            display: inline-block;
        }

        .list-gap > *:not(:last-child) {
            margin-right: 10px;
        }

        .list-gap-2 > *:not(:last-child) {
            margin-right: 20px;
        }

        .text-center {
            text-align: center !important;
        }
    </style>
</head>
<body style="background-color: #fff;">
<div class="box">
    <div class="top-wrap-box">
        <img src="{{asset('public/assets/admin-module/img/email/shift-subscription.png')}}" alt="{{translate('Logo')}}"
             width="140"/>

        <h3 style="margin-top: 20px">{{translate('Subscription Plan Renewed!')}} !</h3>
        <h5>{{translate('Hi')}} {{$provider?->company_name}},</h5>
            <p>{{translate('We\'re excited to inform you that your subscription has been successfully renewed. You can continue to enjoy uninterrupted access to all premium features.')}}
                .</p><br><br>
            <p>{{translate('Click here to visit your store')}}</p>
            <a href="{{route('provider.auth.login')}}">{{route('provider.auth.login')}}</a>
        <hr style="margin-top: 16px; margin-bottom: 16px">
        <p>{{translate('Please contact us for any queries, we’re always happy to help')}}. </p>
        <div>{{translate('Thanks & Regards')}},</div>
        <div style="margin-top: 4px;">{{(business_config('business_name', 'business_information'))->live_values }}</div>
    </div>

    <div class="text-center">
        <ul class="list-inline list-gap-2">
            <li style="margin: 5px"><a href="{{route('page.privacy-policy')}}">{{translate('Privacy Policy')}}</a></li>
            <li style="margin: 5px"><a href="{{route('page.contact-us')}}">{{translate('Contact Us')}}</a></li>
        </ul>

        <div class="list-gap">
            @php($dataValues = business_config('social_media', 'landing_social_media'))
            @foreach($dataValues->live_values??[] as $key=>$item)
                <a href="{{$item['link']}}">
                    <img width="20"
                         src="{{ asset('public/assets/admin-module/img/icons/' . $item['media'] . '.png') }}"
                         alt="{{ translate('image') }}">
                </a>
            @endforeach
        </div>
        <p class="text-center">{{(business_config('business_name', 'business_information'))->live_values }}</p>

    </div>
</div>
</body>
</html>
