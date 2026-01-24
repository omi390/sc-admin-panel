@extends('adminmodule::layouts.master')

@section('title',translate('CronJob list'))

@push('css_or_js')
<link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex gap-2 align-items-center mb-30 justify-content-between">
                <h2 class="page-title">{{translate('CronJob_List')}}</h2>
                <button type="button" class="outline-0 bg-white border rounded-full h-36px px-3" data-bs-toggle="modal" data-bs-target="#howItWorks">
                    <div class="d-flex gap-1 justify-content-end text-primary">
                        <span class="material-symbols-outlined d-inline-flex items-center">help</span>
                        {{translate('How_it_Works')}}
                    </div>
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="badge-soft-secondary rounded mb-4 p-3">
                        <p class="m-0 text-danger">{{ translate('It looks like your server might not have the necessary permissions to automatically set up the cron job. Please ensure that your server has shell/bash access enabled, as it’s required for automated cron job configuration.') }}</p>
                        <p class="m-0 text-danger">{{ translate('If the required permissions are not in place, you’ll need to manually configure the cron job by adding the following command to your server’s crontab') }}</p>
                    </div>
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}" class="search-form search-form_style-two" method="get">
                            <div class="input-group search-form__input_group">
                                <span class="search-form__icon">
                                    <span class="material-icons">search</span>
                                </span>
                                <input type="search" class="theme-input-style search-form__input" name="search" value="{{ request()->search }}" placeholder="{{translate('Search cronjob')}}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="example" class="table align-middle">
                            <thead class="align-middle">
                            <tr>
                                <th>{{translate('Sl')}}</th>
                                <th>{{translate('Job Title')}}</th>
                                <th>{{translate('Mail Send')}}</th>
                                <th>{{translate('Activity')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($lists as $key => $list)
                                <tr>
                                    <td>{{ $lists->firstItem() + $key }}</td>
                                    <td>{{ $list->title }}</td>
                                    <td>{{ $list->send_mail_type ? ucwords($list->send_mail_type) . ' ' . $list->send_mail_day . translate(' days') : translate('not_set') }}</td>
                                    <td>
                                        @if($list->activity)
                                            <span class="{{ $list->activity == 'running' ? 'badge badge badge-success radius-50' : 'badge badge badge-danger radius-50' }}">
                                                {{ ucwords($list->activity) }}
                                            </span>
                                        @else
                                            <span>{{ translate('not_set') }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        <label class="switcher">
                                            <input class="switcher_input route-alert-reload"
                                                   type="checkbox"
                                                   {{ $list?->status ? 'checked' : '' }}
                                                   data-route="{{ route('admin.business-settings.cron-job.status', $list->id) }}"
                                                   data-message="{{ translate('If you turn off this job ' . $list?->title . '. Your user will no longer get the mail at that scheduled time for this specific job') }}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('admin.business-settings.cron-job.edit', $list->id) }}" class="action-btn btn--success" style="--size: 30px">
                                                <span class="material-icons">edit</span>
                                            </a>
                                            <button  type="button"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#viewCronJobs"
                                                     data-name="{{ $list->title }}"
                                                     data-link="{{ route('admin.business-settings.cron-job.edit', $list->id) }}"
                                                     data-send_mail_type="{{ $list->send_mail_type }}"
                                                     data-send_mail_day="{{ $list->send_mail_day }}"
                                                     data-php_file_path="{{ $list->php_file_path }}"
                                                     data-command="{{ $list->command }}"
                                                     class="action-btn btn--light-primary"
                                                     style="--size: 30px">
                                                <span class="material-icons">visibility</span>
                                            </button>
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
                        {!! $lists->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cron Jobs View Modal --}}
    <div class="modal fade" id="viewCronJobs">
        <div class="modal-dialog modal-dialog-centered how-it-works-dialog modal-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0 bg-card position-sticky top-0 z-10">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="p-sm-3 fs-13">
                        <div class="card mb-20">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-100px flex-grow-1">
                                        <h6 class="fs-14 mb-1">{{translate('Job')}}: <label class="job-name"></label></h6>
                                        <div  class="fs-12">{{translate('Subtitle will be get from the info text')}}.</div>
                                    </div>
                                    <div class="executing-time bg--secondary">
                                        <h6 class="fs-14 mb-1">{{translate('Mail send')}}</h6>
                                        <div class="fs-14"><span class="send-mail-type"></span> <span class="send-mail-day"></span>
                                            {{translate('days')}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-20">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{asset('public/assets/admin-module/img/php.png')}}" alt="">
                                    <div class="w-0 flex-grow-1">
                                        <h6 class="fs-12 mb-1">{{translate('PHP File Path')}}</h6>
                                        <div class="fs-12 php-file-path"></div>
                                        <div class="fs-12 text-danger d-flex align-items-start gap-1">
{{--                                            <span class="material-symbols-outlined">warning</span><span class="w-0 flex-grow-1 mt-1">Error: Path or Version Issue</span>--}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-20">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 copy-div pb-2">
                                    <img src="{{asset('public/assets/admin-module/img/command.png')}}" alt="">
                                    <div class="w-0 flex-grow-1">
                                        <h6 class="fs-12 mb-1">{{translate('PHP File Path')}}</h6>
                                        <div class="fs-12 command">/path/to/php/file</div>
                                    </div>
                                    <button type="button" class="btn btn--secondary p-2 copy-button">
                                        <span class="material-symbols-outlined m-0">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <h5 class="fs-16 mb-2">
                            Instructions
                        </h5>
                        <ol class="text--grey instruction-info">
                            <li>
                                You can get the PHP file path from your server. Here is an example of Apache Server -  <br>
                                <strong>/usr/local/bin/php/home/mrfrog/public_html/path/to/cron/script.</strong> <br>
                                This will vary based on servers.
                            </li>
                            <li>
                                Copy the command from here & to write the auto generated Command.
                            </li>
                            <li>
                                Your Cron Job will execute on The Executing Time you have set.
                            </li>
                            <li>
                                Please recheck all link and file before save.
                            </li>
                            <li>
                                <div>
                                    The Cron Job PHP configuration error occurs when
                                </div>
                                <ul class="list-lower-alpha">
                                    <li>
                                        Insert the wrong PHP file path - Copy the correct path and input it in the PHP file path Section
                                    </li>
                                    <li>
                                        <span class="text-dark">PHP version mismatch</span> -  Please go to your cPanel or VPS and check if the PHP version is compatible, then insert the correct PHP path.
                                    </li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer justify-content-end gap-2 border-0 bg-card position-sticky bottom-0">
                    <button class="btn btn--secondary"  data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <a class="btn btn--primary edit-link" href="">Edit</a>
                </div>
            </div>
        </div>
    </div>

    {{-- How It Works Modal --}}
    <div class="modal fade" id="howItWorks">
        <div class="modal-dialog modal-dialog-centered how-it-works-dialog">
            <div class="modal-content border-0">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close invert-1 border-0" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                    <img src="{{asset('/public/assets/admin-module/img/cron-job.png')}}" alt="">
                    <div class="p-4 pb-md-5">
                        <div class="cron-jobs-slider">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="item">
                                        <h4 class="fs-16 mb-3">What is Cron Job</h4>
                                        <p class="m-0 fs-13">
                                            A Cron job is an automated task scheduler that helps run routine processes without manual effort. It can improve efficiency by automating repetitive work and ensuring important tasks happen consistently
                                        </p>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="item">
                                        <h4 class="fs-16 mb-3">Cron Job Title</h4>
                                        <p class="m-0 fs-13">
                                            The title of a Cron Job refers to the command that will be automatically executed at specific time intervals or dates when scheduled.
                                        </p>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="item">
                                        <h4 class="fs-16 mb-3">Executing Time</h4>
                                        <p class="m-0 fs-13">
                                            The execution time of a cron job is defined by the schedule when the job will be done.
                                        </p>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="item">
                                        <h4 class="fs-16 mb-3">PHP File Path</h4>
                                        <p class="m-0 fs-13">
                                            The PHP file path for a Cron Job refers to the location on the server where the PHP script to be executed is stored.
                                        </p>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="item">
                                        <h4 class="fs-16 mb-3">Command</h4>
                                        <p class="m-0 fs-13">
                                            A Cron Job command is the actual instruction used in a Cron scheduler to execute a specific task at defined intervals.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex flex-wrap align-items-center justify-content-between w-100">
                        <button class="btn btn--primary swiper-prev"><span class="material-icons">arrow_back</span>Back</button>
                        <div class="swiper-pagination swiper--pagination"></div>
                        <button class="btn btn--primary swiper-next">Next<span class="material-icons">arrow_forward</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script src="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.js')}}"></script>
<script>
    var swiper = new Swiper('.cron-jobs-slider', {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-next',
            prevEl: '.swiper-prev',
        }
    });

    $('#viewCronJobs').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const modal = $(this);

        modal.find('.job-name').text(button.data('name'));
        modal.find('.job-name').text(button.data('name'));
        modal.find('.send-mail-type').text(button.data('send_mail_type'));
        modal.find('.send-mail-day').text(button.data('send_mail_day'));
        modal.find('.php-file-path').text(button.data('php_file_path'));
        modal.find('.command').text(button.data('command'));
        modal.find('.edit-link').attr('href', button.data('link'));
    });

    $('.copy-button').on('click', function(){
        var commandText = $(this).closest('.copy-div').find('.command').text();

        navigator.clipboard.writeText(commandText).then(function() {
        }).catch(function(error) {
        });
    });

</script>
@endpush
