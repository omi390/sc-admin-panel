<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{translate('invoice')}}</title>
    <link href="{{asset('public/assets/css/bootstrap.min.css')}}" rel="stylesheet" id="bootstrap-css">
    <script src="{{asset('public/assets/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('public/assets/js/jquery.min.js')}}"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .invoice-box-wrap {
            margin-left: auto;
            margin-right: auto;
            max-width: 660px;
        }

        .invoice-box {
            background-color: #f9fcff;
            padding: 1.5rem 1rem;
            font-size: 9px;
            color: #212b36;
        }

        .fs-10 {
            font-size: 10px;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-end {
            align-items: flex-end;
        }

        .align-items-center {
            align-items: center;
        }

        .flex-column {
            flex-direction: column;
        }

        .gap-1 {
            gap: 4px;
        }

        .gap-4 {
            gap: 24px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mt-5 {
            margin-top: 3rem;
        }

        .mb-30 {
            margin-bottom: 30px;
        }

        .text-primary {
            color: #0461a5;
        }

        .invoice-card {
            border-radius: 12px;
            border: 0.75px solid #d7dae0;
            background: #fff;
        }

        .invoice-card__head,
        .invoice-card__body {
            padding: 1rem 1.5rem;
        }

        .invoice-card__head {
            border-bottom: 0.75px solid #d7dae0;
        }

        .meta-info {
            gap: 3rem;
        }

        .border-left {
            border-left: 0.75px solid #d7dae0;
        }

        .table-wrap {
            border-radius: 5px;
            overflow: hidden;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table th,
        table td {
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: rgba(158, 173, 193, 0.1);
        }

        table td {
            background-color: #fcfcfc;
        }

        .invoice-footer {
            border-top: 0.5px solid #ebedf2;
            background: rgba(4, 97, 165, 0.05);
            text-align: center;
            padding: 11px;
            font-size: 10px;
        }

    </style>
</head>
<body>
<div class="invoice-box-wrap" id="invoice">
    <div class="invoice invoice-box">
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex flex-column gap-1">
                <h2>{{translate('INVOICE')}}</h2>
                <div>{{translate('Transaction ID')}}: #{{ $transaction->id }}</div>
                <div>{{translate('Date')}}: {{ \Carbon\Carbon::parse($transaction?->created_at)->format('M d, Y') }}</div>
            </div>

            <div class="d-flex flex-column gap-1 align-items-end text-right">
                @php
                    $logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/media/banner-upload-file.png');

                    $business_name = business_config('business_name','business_information');
                    $business_email = business_config('business_email','business_information');
                    $business_phone = business_config('business_phone','business_information');
                    $business_address = business_config('business_address','business_information');
                @endphp
                <img width="84" height="17"
                     src="{{$logo}}"
                     data-holder-rendered="true"/>
                <div class="mt-2">{{$business_address->live_values}}</div>
                <div>{{$business_phone->live_values}}</div>
                <div>{{$business_email->live_values}}</div>
            </div>
        </div>

        <div class="invoice-card">
            <div class="invoice-card__head">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex gap-4">
                        <div>
                            <div>{{translate('Provider')}}</div>
                            <div class="fs-10">{{ $transaction?->packageLog?->provider?->company_name }}</div>
                        </div>
                        <div>
                            <div>{{translate('Phone')}}</div>
                            <div class="fs-10">{{ $transaction?->packageLog?->provider?->company_phone }}</div>
                        </div>
                        <div>
                            <div>{{translate('Email')}}</div>
                            <div class="fs-10">{{ $transaction?->packageLog?->provider?->company_email }}</div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 align-items-end text-right">
                        <div>{{translate('Invoice of')}} ({{currency_code()}})</div>
                        <h5 class="text-primary fw-700 mb-0 lh-1 mt-1">{{with_currency_symbol($transaction->credit)}}</h5>
                    </div>
                </div>
            </div>
            <div class="invoice-card__body">
                <div class="meta-info d-flex mb-30">
                    <div>
                        <div>{{translate('Payment')}}</div>
                        <div class="fs-10">{{ ucwords(str_replace('_', ' ', $transaction?->packageLog?->payment?->payment_method))  }}</div>
                    </div>
                    <div class="border-left"></div>
                    <div>
                        <div>
                            @php
                                $translationMap = [
                                    'subscription_purchase' => translate('Purchase'),
                                    'subscription_renew' => translate('Purchase'),
                                    'subscription_shift' => translate('Shift'),
                                    'subscription_refund' => translate('Refund')
                                ];

                                $translatedText = $translationMap[$transaction->trx_type] ?? '';
                            @endphp
                            {{ $translatedText }}
                        </div>

                        <div class="fs-10">{{ $transaction?->packageLog?->package_name }}</div>
                    </div>
                    <div class="border-left"></div>
                    <div>
                        @php
                            $start = \Carbon\Carbon::parse($transaction?->packageLog?->start_date)->subDay();
                            $end = \Carbon\Carbon::parse($transaction?->packageLog?->end_date);
                            $duration = $start->diffInDays($end);
                        @endphp
                        <div>{{translate('Duration')}}</div>
                        <div class="fs-10">{{$duration}} {{translate('Days')}}</div>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>{{translate('Transaction ID')}}</th>
                            <th>{{translate('Package Name')}}</th>
                            <th>{{translate('Time')}}</th>
                            <th>{{translate('Validity')}}</th>
                            <th>{{translate('Amount')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction?->packageLog?->package_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction?->created_at)->format('M d, Y') }}</td>
                            <td>{{$duration}} {{translate('Days')}}</td>
                            <td>{{ with_currency_symbol($transaction?->packageLog?->package_price) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 text-center">{{translate('Thanks for using our service')}}.</div>
            </div>
        </div>
    </div>
    <div class="invoice-footer">{{translate('All rights reserved By ')}}@ {{$business_name->live_values}} {{date("Y")}}</div>
</div>


<script>
    "use strict";

    function printContent(el) {
        var restorepage = $('body').html();
        var printcontent = $('#' + el).clone();
        $('body').empty().html(printcontent);
        window.print();
        $('body').html(restorepage);
    }

    printContent('invoice');
</script>
</body>
</html>
