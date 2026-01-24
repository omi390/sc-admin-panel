@extends('adminmodule::layouts.master')

@section('title',translate('CronJob update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-30">
                <h2 class="page-title mb-2">{{ $cronjob->title }}</h2>
                <p>{{translate('Activate this to remind yours customers about subscription renewal.')}}</p>
            </div>
            <div class="card">
                <div class="card-body">
                    <form action="" method="post" class="p-lg-3">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="floating-label-info d-flex align-items-center gap-1 mb-2" id="timing-label">{{ translate('mail send') }}
                                    <i class="material-symbols-outlined" data-bs-toggle="tooltip" data-bs-placement="top"
                                       title="{{ translate('Define the when you want to send the automated message') }}">info</i>
                                </label>
                                <div class="form-floating form-floating__icon">
                                    <select id="mail-timing-select" class="js-select theme-input-style form-control" name="send_mail_type" required>
                                        <option value="before" {{ $cronjob->send_mail_type == 'before' ? 'selected' : '' }}>{{translate('Before')}}</option>
                                        <option value="after" {{ $cronjob->send_mail_type == 'after' ? 'selected' : '' }}>{{translate('After')}}</option>
                                    </select>
                                    <span class="material-symbols-outlined">schedule_send</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="floating-label-info d-flex align-items-center gap-1 mb-2" id="days-label">{{$cronjob->send_mail_type}} days <i class="material-symbols-outlined" data-bs-toggle="tooltip" data-bs-placement="top" title="{{translate('Set the executing time when the job will execute')}}">info</i></label>
                                <div class="form-floating form-floating__icon">
                                    <input type="number" name="send_mail_day" class="form-control py-0 appearance-hidden" value="{{ $cronjob->send_mail_day }}" required>
                                    <span class="material-symbols-outlined">edit_calendar</span>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="floating-label-info d-flex align-items-center gap-1 mb-2">PHP File Path <i class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{translate('Set PHP file path from here. To find out the PHP file path, run "which php" command in your server terminal or ask your server provider')}}">info</i></label>
                                <div class="form-floating form-floating__icon">
                                    <input type="text" name="php_file_path" class="form-control py-0" value="{{ $cronjob->php_file_path ?? '/usr/bin/php' }}" required>
                                    <span class="material-symbols-outlined">conversion_path</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="floating-label-info d-flex align-items-center gap-1 mb-2">Command <i class="material-symbols-outlined" data-bs-toggle="tooltip" title="{{translate('For linux server, add the command in your /etc/crontab file. And for any hosting panel, find add cron option add use the command to create cron job.')}}">info</i></label>
                                <div class="form-floating form-floating__icon disabled-input">
                                    <input type="text" class="form-control copy-input py-0" value="{{ $cronjob->command }}" readonly>
                                    <span class="material-symbols-outlined">code</span>
                                    <button type="button" class="bg--secondary copy-button">
                                        <span class="material-symbols-outlined">content_copy</span>
                                    </button>
                                </div>
                                <small class="mt-3 text-muted">{{translate('Based on the above setup this command will be generated after update')}}</small>
                            </div>
                            <div class="text-end">
                                <div class="d-flex justify-content-end gap-20">
                                    <button class="btn btn--secondary h-45" type="reset">{{translate('Cancel')}}</button>
                                    <button class="btn btn--primary h-45" type="submit">{{translate('Update')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            $('#mail-timing-select').on('change', function () {
                var selectedValue = $(this).val();
                if (selectedValue === 'before') {
                    $('#days-label').text('Before days');
                } else if (selectedValue === 'after') {
                    $('#days-label').text('After days');
                }
            });

            $('.copy-button').on('click', function(){
                $(this).closest('.form-floating').find('.copy-input').select()
                document.execCommand("copy");
            })


        });


    </script>
@endpush
