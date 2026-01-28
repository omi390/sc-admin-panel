@extends('adminmodule::layouts.master')

@section('title', translate('reward_point_usage_history'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('assets/admin-module/plugins/dataTables/jquery.dataTables.min.css') }}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title">{{ translate('Reward Point Usage History') }}</h2>
                        <a href="{{ route('admin.reward-point.config.list') }}" class="btn btn--primary">
                            <span class="material-icons">list</span> {{ translate('Config List') }}
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{ url()->current() }}" method="GET" class="d-flex flex-wrap gap-2 align-items-end">
                                    <div>
                                        <label class="form-label small">{{ translate('User ID') }}</label>
                                        <input type="text" class="theme-input-style" name="user_id"
                                               value="{{ $userId }}" placeholder="UUID">
                                    </div>
                                    <div>
                                        <label class="form-label small">{{ translate('Sub Category ID') }}</label>
                                        <input type="text" class="theme-input-style" name="sub_category_id"
                                               value="{{ $subCategoryId }}" placeholder="UUID">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{ translate('Filter') }}</button>
                                    <a href="{{ route('admin.reward-point.usage') }}" class="btn btn--secondary">{{ translate('Clear') }}</a>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{ translate('Sl') }}</th>
                                        <th>{{ translate('User') }}</th>
                                        <th>{{ translate('Service Variant') }}</th>
                                        <th>{{ translate('Provider') }}</th>
                                        <th>{{ translate('Reward Points') }}</th>
                                        <th>{{ translate('Booking') }}</th>
                                        <th>{{ translate('Date') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($usages as $key => $usage)
                                        <tr>
                                            <td>{{ $usages->firstItem() + $key }}</td>
                                            <td>
                                                @if($usage->user)
                                                    {{ $usage->user->first_name ?? $usage->user->email ?? $usage->user_id }}
                                                @else
                                                    {{ Str::limit($usage->user_id, 8) }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $usage->serviceVariant->variant ?? 'N/A' }}
                                                @if($usage->serviceVariant && $usage->serviceVariant->service)
                                                    <br><small class="text-muted">{{ $usage->serviceVariant->service->name ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $usage->serviceVariant->provider->company_name ?? 'N/A' }}</td>
                                            <td>{{ number_format($usage->reward_points, 3) }}</td>
                                            <td>
                                                @if($usage->booking_id)
                                                    {{ $usage->booking->readable_id ?? $usage->booking_id }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $usage->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">{{ translate('no_data_found') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $usages->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
