@extends('adminmodule::layouts.master')

@section('title', translate('reward_point_configurations'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title">{{translate('Reward Point Configurations')}}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.reward-point.usage') }}" class="btn btn--secondary">
                                <span class="material-icons">history</span> {{translate('Usage History')}}
                            </a>
                            <a href="{{ route('admin.reward-point.config.create') }}" class="btn btn--primary">
                                <span class="material-icons">add</span> {{translate('Configure Reward Points')}}
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $isActive == 'all' ? 'active' : '' }}"
                                   href="{{ url()->current() }}?is_active=all&search={{ $search }}">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $isActive == 'active' ? 'active' : '' }}"
                                   href="{{ url()->current() }}?is_active=active&search={{ $search }}">
                                    {{translate('active')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $isActive == 'inactive' ? 'active' : '' }}"
                                   href="{{ url()->current() }}?is_active=inactive&search={{ $search }}">
                                    {{translate('inactive')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('total')}}:</span>
                            <span class="title-color">{{ $configs->total() }}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{ url()->current() }}" method="GET" class="search-form search-form_style-two">
                                    <input type="hidden" name="is_active" value="{{ $isActive }}">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon"><span class="material-icons">search</span></span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{ $search }}" name="search"
                                               placeholder="{{ translate('search_by_variant_or_provider') }}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{ translate('Sl') }}</th>
                                        <th>{{ translate('Service Variant') }}</th>
                                        <th>{{ translate('Provider') }}</th>
                                        <th>{{ translate('Reward Points') }}</th>
                                        <th>{{ translate('Min Order Amount') }}</th>
                                        <th>{{ translate('Max Uses') }}</th>
                                        <th>{{ translate('Current Uses') }}</th>
                                        <th>{{ translate('Remaining') }}</th>
                                        <th>{{ translate('status') }}</th>
                                        <th>{{ translate('action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($configs as $key => $config)
                                        <tr>
                                            <td>{{ $configs->firstItem() + $key }}</td>
                                            <td>
                                                <strong>{{ $config->serviceVariant->variant ?? 'N/A' }}</strong>
                                                @if($config->serviceVariant && $config->serviceVariant->service)
                                                    <br><small class="text-muted">{{ $config->serviceVariant->service->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $config->serviceVariant->provider->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($config->reward_points, 3) }}</td>
                                            <td>{{ currency_symbol() }}{{ number_format($config->minimum_order_amount ?? 0, 2) }}</td>
                                            <td>{{ $config->max_uses === 0 ? translate('unlimited') : $config->max_uses }}</td>
                                            <td>{{ $config->current_uses }}</td>
                                            <td>
                                                @if($config->max_uses === 0)
                                                    —
                                                @else
                                                    {{ $config->remaining_uses }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $config->is_active ? 'success' : 'secondary' }}">
                                                    {{ $config->is_active ? translate('active') : translate('inactive') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.reward-point.config.edit', $config->id) }}"
                                                       class="action-btn btn--light-primary" style="--size: 30px">
                                                        <span class="material-icons">edit</span>
                                                    </a>
                                                    <button type="button" data-delete="{{ $config->id }}"
                                                            class="action-btn btn--danger" style="--size: 30px">
                                                        <span class="material-symbols-outlined">delete</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.reward-point.config.delete', $config->id) }}"
                                                      method="post" id="delete-{{ $config->id }}" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">{{ translate('no_data_found') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $configs->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        $('.action-btn.btn--danger').on('click', function () {
            let itemId = $(this).data('delete');
            form_alert('delete-' + itemId, '{{ translate('want_to_delete_this') }}?');
        });
    </script>
@endpush
