@extends('adminmodule::layouts.master')

@section('title',translate('Subscribed Provider list'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex gap-2 align-items-center justify-content-between mb-30">
                <div class="page-title-wrap">
                    <h2 class="page-title">{{translate('Subscribed Provider List')}}</h2>
                </div>

                <div class="">
                    <form id="dateRangeForm" action="{{ url()->current() }}" method="get">
                        <select name="date_range" id="date_range" class="js-select form-select">
                            <option value="all_time" selected >{{translate('All_Time')}}</option>
                            <option value="this_year" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_year'?'selected':''}}>{{translate('This_year')}}</option>
                            <option value="this_month" {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_month'?'selected':''}}>{{translate('This_Month')}}</option>
                            <option value="this_week"  {{array_key_exists('date_range', $queryParams) && $queryParams['date_range']=='this_week'?'selected':''}}>{{translate('This_Week')}}</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row mb-4 g-4">
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two">
                                <h2>{{ $packageSubscribers->count() }}</h2>
                                <h3>{{translate('Total Subscription')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov1.png"
                                    class="absolute-img"
                                    alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two success">
                                <h2>{{ $packageSubscribers->where('package_end_date', '>' , \Carbon\Carbon::now())->count() }}</h2>
                                <h3>{{translate('Active_Subscriptions')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov2.png"
                                    class="absolute-img" alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two danger">
                                <h2>{{ $packageSubscribers->where('package_end_date', '<' , \Carbon\Carbon::now())->count() }}</h2>
                                <h3>{{translate('Expired_Subscription')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov3.png"
                                    class="absolute-img"
                                    alt="">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="business-summary style__two warning">
                                <h2>{{$warningSubscribersCount}}</h2>
                                <h3>{{translate('Expiring_Soon ')}}</h3>
                                <img width="35" src="{{asset('public/assets/admin-module')}}/img/icons/ov4.png"
                                    class="absolute-img"
                                    alt="">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 justify-content-between bg-light p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="media gap-2 align-items-center">
                                <img width="28" src="{{asset('public/assets/admin-module')}}/img/icons/tt.svg" class="svg" alt="">
                                <div class="meida-body c1 text-uppercase fs-12">{{translate('TOTAL TRANSACTIONS')}}</div>
                            </div>
                            <div class="c1 fw-semibold">{{ $totalTransactions }}</div>
                        </div>

                        <div class="border-start d-none d-md-block"></div>

                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="media gap-2 align-items-center">
                                <img width="28" src="{{asset('public/assets/admin-module')}}/img/icons/te.svg" class="svg" alt="">
                                <div class="meida-body text-success text-uppercase fs-12">{{translate('TOTAL EARNED')}}</div>
                            </div>
                            <div class="text-success fw-semibold">{{ with_currency_symbol($totalEarning) }}</div>
                        </div>

                        <div class="border-start d-none d-md-block"></div>

                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="media gap-2 align-items-center">
                                <img width="28" src="{{asset('public/assets/admin-module')}}/img/icons/em.svg" class="svg" alt="">
                                <div class="meida-body text-warning text-uppercase fs-12">{{translate('EARNED THIS MONTH')}}</div>
                            </div>
                            <div class="text-warning fw-semibold">{{ with_currency_symbol($totalEarningThisMonth) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="data-table-top d-flex align-items-center flex-wrap gap-10 justify-content-between">
                        <div class="page-title-wrap d-flex gap-2 align-items-center">
                            <h4 class="page-title">{{translate('Provider List')}}</h4>
                            <span class="badge badge-primary fw-semibold">{{ $subscribers->total() }}</span>
                        </div>

                        <div class="d-flex flex-wrap align-items-cente gap-3">
                            <form action="{{ url()->current() }}" class="search-form search-form_style-two" method="get" id="packageSelectForm">
                                <div class="package-subscriber">
                                    <select name="package" id="select_package" class="js-select form-select min-w180">
                                        <option value="all" {{ request('package') == 'all' ? 'selected' : '' }}>All Subscription</option>
                                        @foreach($subscriptions as $subscription)
                                            <option value="{{ $subscription->id }}" {{ request('package') == $subscription->id ? 'selected' : '' }}
                                                {{array_key_exists('package', $queryParams) && $queryParams['package']== $subscription->id ?'selected':''}}>
                                                {{ $subscription->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                            <form action="{{ url()->current() }}" class="search-form search-form_style-two" method="get">
                                <div class="input-group search-form__input_group">
                                    <span class="search-form__icon">
                                        <span class="material-icons">search</span>
                                    </span>
                                    <input type="hidden" name="package" value="{{ $queryParams['package'] }}">
                                    <input type="search" class="theme-input-style search-form__input min-width-300px" name="search" placeholder="{{ translate('search by provider name or package name') }}" value="{{ request('search') }}">
                                </div>
                                <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                            </form>


                        @can('subscriber_export')
                            <div class="dropdown">
                                <button type="button" class="btn btn--secondary text-capitalize dropdown-toggle h-100" data-bs-toggle="dropdown">
                                    <span class="material-icons">file_download</span>
                                    {{translate('download')}}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('admin.subscription.subscriber.download') }}?search={{ $search }}&package={{ $packageId }}">
                                        {{ translate('excel') }}
                                    </a>
                                </ul>
                            </div>
                            @endcan
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="example" class="table align-middle">
                            <thead class="align-middle">
                                <tr>
                                    <th>{{translate('Sl')}}</th>
                                    <th>{{translate('Provider Info')}}</th>
                                    <th>{{translate('Current package')}}</th>
                                    <th>{{translate('Package Price')}}</th>
                                    <th class="text-end">{{translate('Exp Date')}}</th>
                                    <th class="text-end">{{translate('Total Subscription Used')}}</th>
                                    <th class="text-center">{{translate('Is_Trail')}}</th>
                                    <th class="text-center">{{translate('Is_Cancel')}}</th>
                                    <th class="text-center">{{translate('Status')}}</th>
                                    @can('subscriber_view')
                                        <th class="text-center">{{translate('Action')}}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($subscribers as $key => $subscriber)
                                <tr>
                                    <td>{{ $subscribers->firstItem() + $key }}</td>
                                    <td>
                                        @if($subscriber?->provider)
                                            <div class="d-flex gap-1 flex-column">
                                                <a href="{{route('admin.provider.details',[$subscriber?->provider->id, 'web_page'=>'overview'])}}">
                                                    <h6>{{Str::limit($subscriber?->provider?->company_name??'', 30)}}</h6>
                                                </a>
                                                <a class="fs-12 title-color " href="mailto:{{ $subscriber?->provider?->company_email }}">{{ $subscriber?->provider?->company_email }}</a>
                                            </div>
                                        @else
                                            <span class="badge badge-danger">{{ translate('Provider Deleted') }}</span>
                                        @endif

                                    </td>
                                    <td>{{ $subscriber?->package?->name }}</td>
                                    <td>{{ with_currency_symbol($subscriber?->package?->price) }}</td>
                                    <td class="text-end">{{ \Carbon\Carbon::parse($subscriber->package_end_date)->format('M d, Y') }}</td>
                                    <td class="text-end">{{ $subscriber?->logs->where('provider_id', $subscriber->provider_id)->count() }}</td>
                                    <td class="text-center">
                                        @if($subscriber->trial_duration)
                                            <label class="badge badge-success">{{translate('yes')}}</label>
                                        @else
                                            <label class="badge badge-danger">{{translate('no')}}</label>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($subscriber->is_canceled)
                                            <span class="badge badge-success">{{translate('yes')}}</span>
                                        @else
                                            <span class="badge badge-danger">{{translate('no')}}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($subscriber->package_end_date > \Carbon\Carbon::now()->subDay())
                                            <label class="badge badge-success">{{translate('Active')}}</label>
                                        @else
                                            <label class="badge badge-danger">{{translate('Expired')}}</label>
                                        @endif
                                    </td>
                                    @can('subscriber_view')
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('admin.subscription.subscriber.details', [$subscriber->id]) }}?provider_id={{ $subscriber->provider_id }}" class="action-btn btn--light-primary" style="--size: 30px">
                                                    <span class="material-icons">visibility</span>
                                                </a>
                                            </div>
                                        </td>
                                    @endcan
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
                        {!! $subscribers->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script')
    <script>
        $('#select_package').change(function() {
            $('#packageSelectForm').submit();
        });
        $('#date_range').change(function() {
            $('#dateRangeForm').submit();
        });
    </script>
@endpush
