@extends('adminmodule::layouts.master')

@section('title',translate('Subscription Package'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Subscription Package')}}</h2>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('admin/subscription/package/details/*') ? 'active' : ''}}" href="{{ route('admin.subscription.package.details',[ $packageId ]) }}">{{translate('Package_Details')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->is('admin/subscription/package/transactions') ? 'active' : ''}}" href="{{ route('admin.subscription.package.transactions') }}?package_id={{ $packageId }}">{{translate('Transactions')}}</a>
                    </li>
                </ul>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mb-3 title-color fz-16">{{translate('Filter_Option')}}</div>
                    <form
                        action="{{route('admin.subscription.package.transactions', $queryParams)}}"
                        method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 mb-30">
                                <label class="mb-2">{{translate('date_range')}}</label>
                                <select class="js-select" id="date_range" name="date_range">
                                    <option value="all_time" selected >{{translate('All_Time')}}</option>
                                    <option value="this_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year'?'selected':''}}>{{translate('This_year')}}</option>
                                    <option value="this_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_month'?'selected':''}}>{{translate('This_Month')}}</option>
                                    <option value="this_week"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_week'?'selected':''}}>{{translate('This_Week')}}</option>
                                    <option value="custom_date" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'selected':''}}>{{translate('Custom_Date')}}</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-sm-6  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}}  align-self-end"
                                 id="from-filter__div">
                                <div class="form-floating mb-30">
                                    <input type="date" class="form-control" id="from" name="from"
                                           value="{{request('from')}}">
                                    <label for="from">{{translate('From')}}</label>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='custom_date'?'':'d-none'}} align-self-end"
                                 id="to-filter__div">
                                <div class="form-floating mb-30">
                                    <input type="date" class="form-control" id="to" name="to"
                                           value="{{request('to')}}">
                                    <label for="to">{{translate('To')}}</label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit"
                                        class="btn btn--primary btn-sm">{{translate('Filter')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{ url()->current() }}"
                              class="search-form search-form_style-two"
                              method="GET">
                            <input type="hidden" name="provider" value="{{ request('provider') }}">
                            <input type="hidden" name="package" value="{{ request('package') }}">
                            <input type="hidden" name="date_range" value="{{ $queryParams['date_range'] }}">
                            <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                <input type="search" class="theme-input-style search-form__input  min-width-300px" name="search" value="{{ $search }}"
                                       placeholder="{{translate('search by transaction id or provider name')}}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </form>

                        <div class="d-flex flex-wrap gap-3">

                            <div class="dropdown">
                                <button type="button"
                                        class="btn btn--secondary text-capitalize dropdown-toggle h-100"
                                        data-bs-toggle="dropdown">
                                    <span class="material-icons">file_download</span> {{translate('download')}}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.subscription.package.transactions.download') }}?search={{ $search }}&package_id={{request('package_id')}}">
                                            {{translate('Excel')}}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-nowrap">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Transaction_ID')}}</th>
                                <th>{{translate('Transaction_Date')}}</th>
                                <th>{{translate('Provider Info')}}</th>
                                <th>{{translate('Pricing')}}</th>
                                <th>{{translate('Duration')}}</th>
                                <th>{{translate('Payment Status')}}</th>
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($transactions as $key => $transaction)
                                @php
                                    $start = \Carbon\Carbon::parse($transaction?->packageLog?->start_date);
                                    $end = \Carbon\Carbon::parse($transaction?->packageLog?->end_date);
                                    $duration = $start->diffInDays($end);
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaction?->created_at)->format('M d, Y, g:i A') }}</td>
                                    <td>
                                        <div class="d-flex gap-1 flex-column">
                                            <h6>{{ $transaction?->packageLog?->provider?->company_name }}</h6>
                                            <a class="fs-12 title-color " href="mailto:{{ $transaction?->packageLog?->provider?->company_email }}">{{ $transaction?->packageLog?->provider?->company_email }}</a>
                                        </div>
                                    </td>
                                    <td>{{ with_currency_symbol($transaction?->packageLog?->package_price) }}</td>
                                    <td>{{ $duration }} {{translate('days')}}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if( $transaction->trx_type == 'subscription_purchase')
                                                <div class="fs-12">{{translate('Subscribed')}}</div>
                                            @elseif( $transaction->trx_type == 'subscription_renew')
                                                <div class="fs-12">{{translate('Renewal')}}</div>
                                            @elseif( $transaction->trx_type == 'subscription_shift')
                                                <div class="fs-12">{{translate('Migrate to new plan')}}</div>
                                            @endif
                                            <div class="fs-10 c1">{{translate('Paid By')}} {{ ucwords(str_replace('_', ' ', $transaction->packageLog?->payment?->payment_method)) }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{route('admin.subscription.package.transactions.invoice',[$transaction->id])}}" class="action-btn btn--light-primary" style="--size: 30px" target="_blank">
                                                <span class="material-icons">print</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14"><p class="text-center">{{translate('no_data_available')}}</p></td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $transactions->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        "use strict";

        $(document).ready(function () {
            $('#date_range').on('change', function () {
                if (this.value === 'custom_date') {
                    $('#from-filter__div').removeClass('d-none');
                    $('#to-filter__div').removeClass('d-none');
                }

                if (this.value !== 'custom_date') {
                    $('#from-filter__div').addClass('d-none');
                    $('#to-filter__div').addClass('d-none');
                }
            });
        });

    </script>
@endpush
