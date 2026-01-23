@extends('adminmodule::layouts.master')

@section('title',translate('Language Setup'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Translated_Content ')}}</h2>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <div class="data-table-top d-flex align-items-center flex-wrap gap-10 justify-content-between mb-0">
                        <form action="#"
                                class="search-form search-form_style-two"
                                method="GET">
                            @csrf
                            <div class="input-group search-form__input_group">
                            <span class="search-form__icon">
                                <span class="material-icons">search</span>
                            </span>
                                <input type="search" class="theme-input-style search-form__input" name="search"
                                        placeholder="{{translate('search_here')}}"
                                        value="{{ request()?->search ?? null }}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                        <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#translation-warning-modal">
                            {{ translate('Translate All') }}
                        </button>
                    </div>
                    <input type="hidden" value="0" id="translating-count">
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="example" class="table table-bordered align-middle">
                            <thead class="text-nowrap bg-transparent">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Current_Value')}}</th>
                                <th>{{translate('Translated_Value')}}</th>
                                @can('configuration_update')
                                    <th>{{translate('Auto_Translate')}}</th>
                                    <th>{{translate('Update')}}</th>
                                @endcan
                            </tr>
                            </thead>
                            <tbody>
                            @php($count=0)
                            @foreach($fullData as $key=>$value)
                                @php($count++)
                                <tr id="lang-{{$count}}">
                                    <td>{{ $count+$fullData->firstItem() -1}}</td>
                                    <td>
                                        <input type="text" name="key[]"
                                                value="{{$key}}" hidden>
                                        <label>{{$key }}</label>
                                    </td>
                                    <td class="lan-key-name">
                                        <input type="text" class="form-control" name="value[]"
                                                id="value-{{$count}}" value="{{$fullData[$key]}}">
                                    </td>
                                    @can('configuration_update')
                                        <td>
                                            <button class="btn btn--light-primary auto-translate-data"
                                                    data-key="{{$key}}"
                                                    data-id="{{$count}}">
                                                <span class="material-icons m-0">translate</span>
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn--primary update-language-data" type="button"
                                                    data-key="{{urlencode($key)}}"
                                                    data-id="{{$count}}">
                                                <span class="material-icons m-0">save</span>
                                            </button>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($fullData) !== 0)
                            <hr>
                        @endif
                        <div class="page-area">
                            <div class="d-flex justify-content-end">
                                {!! $fullData->withQueryString()->links() !!}
                            </div>
                        </div>
                        @if(count($fullData) === 0)
                            <div class="empty--data text-center">
                                <h5>
                                    {{translate('no_data_found')}}
                                </h5>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade language-complete-modal" id="complete-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center px-5">
                        <button type="button" class="btn-close location_reload"></button>
                        <div class="py-5">
                            <div class="mb-4">
                                <img src="{{asset('/public/assets/admin-module/img/language-complete.png')}}" alt="">
                            </div>
                            <h4 class="mb-3">
                                {{ translate('Your_file_has_been_successfully_translated') }}
                            </h4>
                            <p class="mb-4 text-9EADC1">
                                {{translate(' Your all selected items that you wanted to translate those successfully translated &
                                do not forget to click the save button, otherwise file will not be translated.')}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade language-warning-modal" id="warning-modal" tabindex="-1" aria-labelledby="exampleModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="d-flex gap-3 align-items-start">
                            <img src="{{asset('/public/assets/admin-module/img/invalid-icon.png')}}" alt="">
                            <div class="w-0 flex-grow-1">
                                <h3>{{ translate('Warning!') }}</h3>
                                <span class="pt-2">{{ translate('Translating in progress. Are you sure, want to close this tab?')}}</span>
                                <p  class="pb-2">{{ translate('If you close the tab, the progress made will be saved but remaining content will not be translated.') }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">{{ translate('Cancel') }}</button>
                            <button type="button" class="btn btn--primary location_reload" id="close-tab" >{{ translate('Yes,_Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="translation-warning-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body modal-body-color-border">
                        <div class="d-flex gap-3 mb-3">
                            <div>
                                <img src="{{asset('/public/assets/admin-module/img/invalid-icon.png')}}" alt="">
                            </div>
                            <div class="w-0 flex-grow-1">
                                <h3 class="mb-2">{{ translate('warning') }}!</h3>
                                <p class="mb-0">
                                    {{ translate('are_you_sure,_want_to_start_auto_translation') }} ?
                                </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            @if(env('APP_ENV') != 'demo')
                                <button type="button" class="btn btn--primary" data-bs-dismiss="modal" id="translating-modal-start">
                                    {{ translate('Continue') }}
                                </button>
                            @else
                                <button type="button" class="btn btn-primary demo_check" data-bs-dismiss="modal">
                                    {{ translate('Continue') }}
                                </button>
                            @endif

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                {{ translate('cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade language-complete-modal" id="translating-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center px-5">
                        <button type="button" class="btn-close" id="translateCancel"></button>
                        <div class="py-5 px-sm-2">
                            <div class="progress-circle-container mb-4">
                                <img width="80px" src="{{asset('/public/assets/admin-module/img/loader-icon.gif')}}" alt="{{translate('progress')}}">
                            </div>
                            <h4 class="mb-2">{{ translate('Translating_may_take_up_to') }} <span id="time-data"> {{ translate('Hours') }}</span></h4>
                            <p class="mb-4">
                                {{ translate('Be patient, don’t close or terminate your tab or browser.') }}
                            </p>
                            <div class="max-w-215px mx-auto">
                                <div class="d-flex flex-wrap mb-1 justify-content-between font-semibold text--title">
                                    <span>{{ translate('in_Progress') }}</span>
                                    <span class="translating-modal-success-rate">0.4%</span>
                                </div>
                                <div class="upload--progress progress mb-3 h-5px">
                                    <div class="progress-bar bg-success rounded-pill translating-modal-success-bar" style="width: 0.4%"></div>
                                </div>
                            </div>
                            <p class="mb-4 text-9EADC1">
                                {{ translate('If you close now, the progress made so far will be saved. The remaining content will not be translated.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            $('#translating-modal-start').on('click', function() {
                autoTranslationFunction();
            });

            $(document).on('click', '.location_reload', function () {
                $(window).off('beforeunload');
                window.location.reload();
            });


            $(document).on('click', '.close-tab', function () {
                $('#translating-modal').removeClass('prevent-close');
                window.close();
            });

            $(document).on('click', '#translateCancel', function () {
                $('#warning-modal').modal('show');
                $('#translating-modal').css({
                    opacity: "0.3",
                    pointerEvents: "none"
                })
            });

            $(document).on('click', '#cancelBtn', function () {
                $('#warning-modal').modal('hide');
                $('#translating-modal').css({
                    opacity: "1",
                    pointerEvents: ""
                })
            });

            function autoTranslationFunction() {
                var translatingCount = $('#translating-count').val();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('admin.language.auto-translate-all', [$lang]) }}",
                    method: 'GET',
                    data: { translating_count: translatingCount },
                    beforeSend: function () {
                        $('#translating-modal').addClass('prevent-close');
                        $('#translating-modal').modal('show');
                    },
                    success: function (response) {
                        if (response.data === 'data_prepared') {
                            $('#translating-count').val(response.total);
                            autoTranslationFunction();
                        } else if (response.data === 'translating' && response.status === 'pending') {
                            if ($('#translating-count').val() == 0) {
                                $('#translating-count').val(response.total);
                            }

                            updateProgress(response.percentage);
                            updateTimeRemaining(response.hours, response.minutes, response.seconds);

                            autoTranslationFunction();
                        } else if ((response.data === 'translating' && response.status === 'done') ||
                            response.data === 'success' || response.data === 'error') {
                            $('#translating-modal').removeClass('prevent-close');
                            $('#translating-modal').addClass('d-none');
                            $('#translating-count').val(0);

                            console.log('gfhfhfgh')
                            if (response.data !== 'error') {
                                $('#complete-modal').modal('show');
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    },
                    complete: function () {
                    }
                });
            }

            function updateProgress(percentage) {
                percentage = Math.max(1, Math.round(percentage));
                $('.translating-modal-success-rate').html(percentage + '%');
                $('.translating-modal-success-bar').css('width', percentage + '%');
                // setProgress(percentage);
            }

            function updateTimeRemaining(hours, minutes, seconds) {
                let timeString = '';
                if (hours > 0) {
                    timeString = hours + ' {{ translate('hours') }} ' + minutes + ' {{ translate('min') }}';
                } else if (minutes > 0) {
                    timeString = minutes + ' {{ translate('min') }} ' + seconds + ' {{ translate('seconds') }}';
                } else if (seconds > 0) {
                    timeString = seconds + ' {{ translate('seconds') }}';
                }
                $('#time-data').html(timeString);
            }

            const modal = document.getElementById('translating-modal');
            window.addEventListener('beforeunload', (event) => {
                if (modal.classList.contains('prevent-close')) {
                    event.preventDefault();
                    event.returnValue = '';
                } else {
                    $('#translating-modal').modal('hide');
                }
            });

            // function setProgress(percentage) {
            //     const circle = $('.progress-circle .progress');
            //     const radius = circle.attr('r');
            //     const circumference = 2 * Math.PI * radius;
            //     const offset = circumference - (percentage / 100 * circumference);
            //
            //     circle.css('stroke-dashoffset', offset);
            // }
            // setTimeout(() => setProgress(87), 1000);
        });
    </script>


    <script>
        "use strict";

        $(".update-language-data").on('click', function () {
            let key = $(this).data('key');
            let id = $(this).data('id');
            let value = $('#value-' + id).val()
            update_lang(key, value);
        })

        function update_lang(key, value) {
            console.log(key);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.language.translate-submit',[$lang])}}",
                method: 'POST',
                data: {
                    key: key,
                    value: value
                },

                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    toastr.success('{{translate('text_updated_successfully')}}');
                },
                complete: function () {
                    $('.preloader').hide();
                },
            });
        }

        $(".auto-translate-data").on('click', function () {
            let key = $(this).data('key');
            let id = $(this).data('id');
            auto_translate(key, id);
        })

        function auto_translate(key, id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.language.auto-translate',[$lang])}}",
                method: 'POST',
                data: {
                    key: key
                },
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    toastr.success('{{translate('Key translated successfully')}}');
                    console.log(response.translated_data)
                    $('#value-' + id).val(response.translated_data);
                },
                complete: function () {
                    $('.preloader').hide();
                },
            });
        }
    </script>
@endpush
