@extends('providermanagement::layouts.master')

@section('title',translate('Profile_Update'))

@push('css_or_js')

    <link rel="stylesheet" href="{{asset('assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">

    <style>
        .location_map_div {
            height: 250px;
        }

        .location_map_canvas {
            height: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex justify-content-between">
                        <div>
                            <h2 class="page-title">{{translate('Update_Profile')}}</h2>
                        </div>
                        <?php
                        $provider_self_delete = business_config('provider_self_delete', 'provider_config')->live_values ?? 0;
                        ?>

                        @if($provider_self_delete)
                            <div class="text-danger">
                            <span
                                class="btn btn-danger gap-2 d-flex provider-delete"
                                data-provider="delete-{{auth()->user()->id}}">
                                <span class="material-icons m-0">delete</span>{{translate('Delete Account')}}
                            </span>
                                <form
                                    action="{{route('provider.delete_account',[auth()->user()->id])}}"
                                    method="post" id="delete-{{auth()->user()->id}}"
                                    class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('provider.profile_update') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="c1 mb-30">{{translate('General_Information')}}</h4>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" class="form-control" name="company_name"
                                                   value="{{ $provider->company_name }}"
                                                   placeholder="{{translate('Company_/_Individual_Name')}}">
                                            <label>{{translate('Company_/_Individual_Name')}}</label>
                                            <span class="material-icons">store</span>
                                        </div>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="email" class="form-control" name="company_email"
                                                   value="{{ $provider->company_email }}"
                                                   placeholder="{{translate('Company_Email')}}">
                                            <label>{{translate('Company_Email')}}</label>
                                            <span class="material-icons">mail</span>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <label for="company_phone">{{translate('Company_Phone')}}</label>
                                            <input
                                                oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                type="tel" class="form-control company_phone phone-input-with-country-picker iti__tel-input"
                                                name="company_phone" id="company_phone"
                                                value="{{ $provider->company_phone }}"
                                                placeholder="{{translate('Company_Phone')}}">
                                            <div class="">
                                                <input type="text" class="country-picker-phone-number w-50" value="{{old('company_phone')}}" name="company_phone" hidden  readonly>
                                            </div>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <select class="select-zone theme-input-style w-100" name="zone_id" required>
                                                <option selected disabled>{{translate('Select_Zone')}}</option>
                                                @foreach($zones as $zone)
                                                    <option
                                                        value="{{$zone->id}}" {{ $provider->zone->id == $zone->id ? 'selected' : '' }}>{{$zone->name}}</option>
                                                @endforeach
                                            </select>
                                            <small
                                                class="d-block mt-1 text-danger">* {{translate('Update your latitude & longitude according to the selected zone')}}</small>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <textarea class="form-control resize-none" name="company_address"
                                                      placeholder="{{translate('Company_Address')}}">{!! $provider->company_address !!}</textarea>
                                            <label>{{translate('Company_Address')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column align-items-center gap-3">
                                            <h3 class="mb-0">{{translate('Company_Logo')}}</h3>
                                            <div>
                                                <div class="upload-file">
                                                    <input type="file" class="upload-file__input" name="logo" accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                    <div class="upload-file__img">
                                                        <img src="{{$provider->logo_full_path}}" alt="{{ translate('image') }}">
                                                    </div>
                                                    <span class="upload-file__edit">
                                                        <span class="material-icons">edit</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="opacity-75 max-w220 mx-auto">{{translate('Image format - jpg, png,
                                                jpeg,
                                                gif Image
                                                Size -
                                                maximum size 2 MB Image Ratio - 1:1')}}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h4 class="c1 mb-30">{{translate('Account_Information')}}</h4>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <span class="form-control opacity-50"
                                                  data-bs-toggle="tooltip" data-bs-placement="top"
                                                  data-bs-custom-class="custom-tooltip"
                                                  data-bs-title="{{translate('Not_editable')}}">
                                                {{ $provider->owner->email }}
                                            </span>
                                            <label>{{translate('Email')}}</label>
                                            <span class="material-icons">mail</span>
                                        </div>

                                        <div class="form-floating mb-30">
                                            <label for="account_phone">{{translate('Phone')}}</label>
                                            <input
                                                oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                type="tel" class="form-control company_phone phone-input-with-country-picker3 iti__tel-input" name="account_phone"
                                                id="account_phone"
                                                value="{{ $provider->owner->phone }}"
                                                placeholder="{{translate('Phone')}}">
                                            <div class="">
                                                <input type="text" class="country-picker-phone-number3 w-50" value="{{old('account_phone')}}" name="account_phone" hidden  readonly>
                                            </div>
                                        </div>

                                        <div class="row gx-2">
                                            <div class="col-lg-6">
                                                <div class="form-floating form-floating__icon mb-30">
                                                    <input type="password" class="form-control" name="password"
                                                           placeholder="{{translate('Password')}}"
                                                           autocomplete="off">
                                                    <label>{{translate('Password')}}</label>
                                                    <span class="material-icons">lock</span>
                                                    <span class="material-icons togglePassword">visibility_off</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-floating form-floating__icon mb-30">
                                                    <input type="password" class="form-control" name="confirm_password"
                                                           placeholder="{{translate('Confirm_Password')}}"
                                                           autocomplete="off">
                                                    <label>{{translate('Confirm_Password')}}</label>
                                                    <span class="material-icons">lock</span>
                                                    <span class="material-icons togglePassword">visibility_off</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-30">
                                            <h4 class="c1">{{translate('Contact_Person')}}</h4>
                                        </div>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" class="form-control" name="contact_person_name"
                                                   value="{{ $provider->contact_person_name }}"
                                                   placeholder="{{translate('Name')}}">
                                            <label>{{translate('Name')}}</label>
                                            <span class="material-icons">account_circle</span>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <label for="contact_person_phone">{{translate('Phone')}}</label>
                                            <input
                                                oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                                type="tel" class="form-control company_phone phone-input-with-country-picker2 iti__tel-input" name="contact_person_phone"
                                                id="contact_person_phone"
                                                value="{{ $provider->contact_person_phone }}"
                                                placeholder="{{translate('Phone')}}">
                                            <div class="">
                                                <input type="text" class="country-picker-phone-number2 w-50" value="{{old('contact_person_phone')}}" name="contact_person_phone" hidden  readonly>
                                            </div>
                                        </div>
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="email" class="form-control" name="contact_person_email"
                                                   value="{{ $provider->contact_person_email }}"
                                                   placeholder="{{translate('Business_Email')}}">
                                            <label>{{translate('Business_Email')}}</label>
                                            <span class="material-icons">mail</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="mb-30">
                                                    <div class="form-floating form-floating__icon">
                                                        <input type="text" class="form-control" name="latitude"
                                                               id="latitude"
                                                               placeholder="{{translate('latitude')}} *"
                                                               value="{{$provider->coordinates['latitude'] ?? null}}"
                                                               required readonly
                                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                                               title="{{translate('Select from map')}}">
                                                        <label>{{translate('latitude')}} *</label>
                                                        <span class="material-icons">location_on</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="mb-30">
                                                    <div class="form-floating form-floating__icon">
                                                        <input type="text" class="form-control" name="longitude"
                                                               id="longitude"
                                                               placeholder="{{translate('longitude')}} *"
                                                               value="{{$provider->coordinates['longitude'] ?? null}}"
                                                               required readonly
                                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                                               title="{{translate('Select from map')}}">
                                                        <label>{{translate('longitude')}} *</label>
                                                        <span class="material-icons">location_on</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div id="location_map_div" class="location_map_div">
                                                    <input id="pac-input" class="form-control w-auto"
                                                           data-toggle="tooltip"
                                                           data-placement="right"
                                                           data-original-title="{{ translate('search_your_location_here') }}"
                                                           type="text" placeholder="{{ translate('search_here') }}"/>
                                                    <div id="location_map_canvas"
                                                         class="overflow-hidden rounded location_map_canvas"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-4 flex-wrap justify-content-end mt-4">
                                    <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                    <button type="submit"
                                            class="btn btn--primary demo_check">{{translate('Update')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="alertModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('/public/assets/provider-module/img/profile-delete.png')}}" alt="">
                        <h3>{{translate('Sorry you can’t delete your account !')}}!</h3>
                        <p class="fw-medium">
                            {{translate('Please complete your ongoing and accepted bookings')}}
                        </p>
                        <a href="{{route('provider.booking.list', ['booking_status' => 'accepted'])}}">
                            <button type="reset" class="btn btn--primary">{{translate('Booking Request')}}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="alertModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('/public/assets/provider-module/img/profile-delete.png')}}" alt="">
                        <h3>{{translate('Sorry you can’t delete your account !')}}!</h3>
                        <p class="fw-medium">
                            {{translate('You have cash in hand, you have to pay the due to delete your account.')}}
                        </p>
                        <a href="{{route('provider.account_info', ['page_type'=>'overview'])}}">
                            <button type="reset" class="btn btn--primary">{{translate('Pay the Due')}}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script src="{{asset('assets/provider-module')}}/js/spartan-multi-image-picker.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{business_config('google_map', 'third_party')?->live_values['map_api_key_client']}}&libraries=places&v=3.45.8"></script>


    <script>
        "use strict";

        $('.provider-delete').on('click', function () {
            let provider = $(this).data('provider');
            let message = "{{(translate('want_to_delete_your_account'))}}"
            if ('{{env('APP_ENV')=='demo'}}') {
                toastr.info('This function is disable for demo mode', {
                    CloseButton: true,
                    ProgressBar: true
                });
            } else {
                if ("{{$acceptedBookings}}" != 0 || "{{$ongoingBookings}}" != 0) {
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exampleModal'))
                    modal.show();
                } else if ("{{$account->account_payable != 0}}" || "{{$account->account_receivable != 0}}") {
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('accountModal'))
                    modal.show();
                } else {
                    form_alert(provider, message)
                }
            }
        });

        $("#multi_image_picker").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 2,
                rowHeight: '10%',
                groupClassName: 'col-3',
                dropFileLabel: "{{translate('Drop_here')}}",
                placeholderImage: {
                    image: '{{asset('assets/provider-module')}}/img/media/upload-file.png',
                    width: '75%',
                },

                onRenderedPreview: function (index) {
                    toastr.success('{{translate('Image_added')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onRemoveRow: function (index) {
                    console.log(index);
                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate('Please_only_input_png_or_jpg_type_file')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate('File_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }
        );
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });


        $(document).ready(function () {
            function initAutocomplete() {
                var myLatLng = {

                    lat: 23.811842872190343,
                    lng: 90.356331
                };
                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: {
                        lat: 23.811842872190343,
                        lng: 90.356331
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    var coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];


                    geocoder.geocode({
                        'latLng': latlng
                    }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('address').innerHtml = results[1].formatted_address;
                            }
                        }
                    });
                });
                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });
                let markers = [];
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var mrkr = new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });
                        google.maps.event.addListener(mrkr, "click", function (event) {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position.lng();
                        });

                        markers.push(mrkr);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete();
        });


        $('.__right-eye').on('click', function () {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active')
                $(this).find('i').removeClass('tio-invisible')
                $(this).find('i').addClass('tio-hidden-outlined')
                $(this).siblings('input').attr('type', 'password')
            } else {
                $(this).addClass('active')
                $(this).siblings('input').attr('type', 'text')


                $(this).find('i').addClass('tio-invisible')
                $(this).find('i').removeClass('tio-hidden-outlined')
            }
        })
    </script>
@endpush
