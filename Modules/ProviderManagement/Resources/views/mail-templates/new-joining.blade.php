<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{translate('New joining')}}</title>
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

        .btn--primary {
            background-color: #4153b3;
            color: #fff !important;
        }

        .btn--secondary {
            background-color: transparent;
            color: #000 !important;
            border: 1px solid #0c63e4;
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
        .box{
            background-color: #fff;
            width: 500px;
            margin-left: auto;
            margin-right: auto;
            padding: 45px 40px 44.579px 40px;

        }
        .table-wrap{
            border: 1px solid #E6E6E6;
            padding: 16px;
            border-radius: 5px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="box">
    <div class="top-wrap-box">
        @php($logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/placeholder.png'))
        <img src="{{$logo}}" alt="{{translate('Logo')}}" width="140"/>

        <h3 style="margin-top: 20px">{{translate('New Joining Request')}} !</h3>
        <h5>{{translate('Hi')}} {{translate('admin')}},</h5>
        <p>{{translate('A new joining request has been submitted by')}} {{ $provider->company_name }} {{translate('to')}}
            {{ business_config( 'business_name', 'business_information')->live_values }}. {{translate('Here are the key details:')}}</p><br>
        <div class="table-wrap">
            <table>
                <tbody>
                <tr>
                    <td>{{ translate('Provider Name: ') }}</td>
                    <td>{{ $provider->company_name }}<</td>
                </tr>
                <tr>
                    <td>{{ translate('Email & Phone: ') }}</td>
                    <td>{{ $provider->company_email }}, {{ $provider->company_phone }}</td>
                </tr>
                <tr>
                    <td>{{ translate('Business Name: ') }}</td>
                    <td>{{ $provider->company_name }}</td>
                </tr>
                <tr>
                    <td>{{ translate('Address: ') }}</td>
                    <td>{{ $provider->company_address }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex gap-20 mt-30">
            <a href="{{route('admin.provider.onboarding_details',[$provider->id])}}" class="btn btn--primary">{{ translate('View Details') }}</a>
            <a href="{{route('admin.provider.onboarding_request', ['status'=>'onboarding'])}}" class="btn btn--secondary" type="submit">{{ translate('View All Request') }}</a>
        </div>
        <hr style="margin-top: 16px; margin-bottom: 16px">
        <p>{{translate('Please contact us for any queries, we’re always happy to help')}}. </p>
        <div>{{translate('Thanks & Regards')}},</div>
        <div style="margin-top: 4px;">{{(business_config('business_name', 'business_information'))->live_values }}</div>
    </div>

    <div class="text-center">
        <ul class="list-inline list-gap-2">
            <li><a href="{{route('page.privacy-policy')}}">{{translate('Privacy Policy')}}</a></li>
            <li><a href="{{route('page.contact-us')}}">{{translate('Contact Us')}}</a></li>
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
        <p class="text-center">{{translate('Copyright')}}
            {{date('Y')}} {{(business_config('business_name', 'business_information'))->live_values }}
            . {{translate('All right reserved')}}</p>
    </div>
</div>
</body>
</html>
