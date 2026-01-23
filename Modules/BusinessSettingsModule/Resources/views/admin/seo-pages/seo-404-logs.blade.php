@extends('adminmodule::layouts.master')

@section('title',translate('seo settings'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            {{--@include('businesssettingsmodule::admin.seo-pages._new-nav')--}}
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="">
                        <h3 class="title mb-2">{{translate('404 Logs')}}</h3>
                        <p class="m-0">{{translate("Logs track instances where users encounter 'page not found' errors on a website")}}
                            <a href="https://6amtech.com/blog/404-logs/" class="text-primary text-underline font-semibold">{{translate('Learn more')}}</a>
                        </p>
                    </div>
                    <div>
                        <a href="javascript:void(0);" class="btn bg-soft-danger text-danger border border-danger" id="clear-all-log">{{translate('Clear All Log')}}</a>
                    </div>
                </div>

                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="thead-light">
                            <tr>
                                <th class="w-95px">
                                    <div class="d-flex align-items-center gap-2">
                                <span class="check-item-2">
                                    <input type="checkbox" id="selectAll">
                                </span>
                                        <span>{{translate('URL')}}</span>
                                    </div>
                                </th>
                                <th class="w-45px text-center">{{translate('Hits')}}</th>
                                <th class="w-200px text-center">{{translate('Last Hit Date')}}</th>
                                <th class="w-200px text-center">{{translate('Redirection Link')}}</th>
                                <th class="text-center w-60px">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($errorLogs as $key => $errorLog)
                                <tr data-id="{{$errorLog->id}}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                <span class="check-item-2">
                                    <input type="checkbox" name="url" value="{{ $errorLog->id }}">
                                </span>
                                            <a href="javascript:" class="text-primary text-underline">{{ $errorLog->url }}</a>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-semibold text-title">{{ $errorLog->hit_counts }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span>{{date('d-M-Y',strtotime($errorLog->updated_at))}} <small>{{date('h:ia',strtotime($errorLog->updated_at))}}</small></span>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            @if($errorLog->redirect_url)
                                                <button type="button" class="btn btn--primary btn-outline-primary edit-content-btn text-capitalize"
                                                        data-bs-toggle="modal" data-bs-target="#modal"
                                                        data-redirection_link="{{$errorLog->redirect_url}}"
                                                        data-action="{{ route('admin.business-settings.seo.error-log-link', $errorLog->id) }}">
                                                    <span class="material-symbols-outlined">edit</span>
                                                    <span>{{translate('edit_link')}}</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn--primary btn-outline-primary add-content-btn text-capitalize"
                                                        data-bs-toggle="modal" data-bs-target="#modal"
                                                        data-redirection_link="{{$errorLog->redirect_url}}"
                                                        data-action="{{ route('admin.business-settings.seo.error-log-link', $errorLog->id) }}">
                                                    <span class="material-symbols-outlined">add</span>
                                                    <span>{{ translate('Add Link')}}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap justify-content-center">
                                            <button type="button"
                                                    data-id="delete-{{$errorLog->id}}"
                                                    data-message="{{translate('want_to_delete_this_log')}}?"
                                                    class="action-btn btn--danger {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                                    style="--size: 30px">
                                                                    <span
                                                                            class="material-symbols-outlined">delete</span>
                                            </button>
                                            <form
                                                    action="{{route('admin.business-settings.seo.error-log-destroy',[$errorLog->id])}}"
                                                    method="post" id="delete-{{$errorLog->id}}"
                                                    class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="5">{{translate('data not found')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end m-3">
                        {!! $errorLogs->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title w-100 text-center">{{translate('Redirection Link')}}</h3>
                    <div type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </div>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="redirection_link">
                                <label class="form-label">{{translate('Redirection Link')}}</label>
                            </div>
                        </div>
                        <div class="mb-3 btn--container justify-content-end">
                            <button type="submit" class="btn btn--primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict"
        $('#modal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const modal = $(this);
            const redirectionLink = button.data('redirection_link');
            const action = button.data('action');

            modal.find('input[name=redirection_link]').val(redirectionLink);
            modal.find('form').attr('action',action);
        });

        $(document).ready(function() {
            // Initially hide the "Clear All Log" button
            $('#clear-all-log').hide();

            // Handle individual checkbox change
            $('input[name="url"]').on('change', function() {
                let allChecked = $('input[name="url"]').length === $('input[name="url"]:checked').length;
                $('#selectAll').prop('checked', allChecked); // Auto-select or deselect "Select All" based on individual checkbox states

                let anyChecked = $('input[name="url"]:checked').length > 0;
                $('#clear-all-log').toggle(anyChecked); // Show/hide based on selection
            });

            // Handle "Select All" checkbox
            $('#selectAll').on('change', function() {
                $('input[name="url"]').prop('checked', this.checked);
                let anyChecked = $('input[name="url"]:checked').length > 0;
                $('#clear-all-log').toggle(anyChecked); // Show/hide based on selection
            });

            // Handle "Clear All Log" button click event
            $('#clear-all-log').on('click', function(e) {
                e.preventDefault();

                // Get all selected checkbox values (log IDs)
                let selectedLogs = $('input[name="url"]:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedLogs.length > 0) {
                    if (confirm('Are you sure you want to delete the selected logs?')) {
                        // Send selected log IDs to the server for bulk deletion
                        $.ajax({
                            url: '{{ route("admin.business-settings.seo.error-log-bulk-destroy") }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                log_ids: selectedLogs
                            },
                            success: function(response) {
                                // Reload the page to reflect the changes
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                alert('Error deleting logs.');
                            }
                        });
                    }
                }
            });
        });

    </script>
@endpush
