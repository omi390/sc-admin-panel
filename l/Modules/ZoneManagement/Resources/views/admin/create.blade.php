@extends('adminmodule::layouts.master')

@section('title',translate('zone_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/css/zone-module.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('zone_setup')}}</h2>
                    </div>

                    @can('zone_add')
                        <div class="card zone-setup-instructions mb-30">
                            <div class="card-body p-30">
                                <form id="zone-form" action="{{route('admin.zone.store')}}"
                                      enctype="multipart/form-data"
                                      method="POST">
                                    @csrf
                                    <div class="row justify-content-between">
                                        <div class="col-lg-5 col-xl-4 mb-5 mb-lg-0">
                                            <h4 class="mb-3 c1">{{translate('instructions')}}</h4>
                                            <div class="d-flex flex-column">
                                                <p>{{translate('create_zone_by_click_on_map_and_connect_the_dots_together')}}</p>

                                                <div class="media mb-2 gap-3 align-items-center">
                                                    <img
                                                        src="{{asset('public/assets/admin-module/img/icons/map-drag.png')}}"
                                                        alt="{{ translate('image') }}">
                                                    <div class="media-body ">
                                                        <p>{{translate('use_this_to_drag_map_to_find_proper_area')}}</p>
                                                    </div>
                                                </div>

                                                <div class="media gap-3 align-items-center">
                                                    <img
                                                        src="{{asset('public/assets/admin-module/img/icons/map-draw.png')}}"
                                                        alt="{{ translate('image') }}">
                                                    <div class="media-body ">
                                                        <p>{{translate('click_this_icon_to_start_pin_points_in_the_map_and_connect_them_to_draw_a_
                                                        zone_._Minimum_3_points_required')}}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="map-img mt-4">
                                                    <img class="dark-support"
                                                         src="{{asset('public/assets/admin-module/img/instructions.gif')}}"
                                                         alt="{{ translate('image') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                            @if($language)
                                                <ul class="nav nav--tabs border-color-primary mb-4">
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link active"
                                                           href="#"
                                                           id="default-link">{{translate('default')}}</a>
                                                    </li>
                                                    @foreach ($language?->live_values as $lang)
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link"
                                                               href="#"
                                                               id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @if($language)
                                                <div class="form-floating form-floating__icon mb-30 lang-form"
                                                     id="default-form">
                                                    <input type="text" name="name[]" class="form-control"
                                                           placeholder="{{translate('zone_name')}}" required>
                                                    <label>{{translate('zone_name')}} ({{ translate('default') }}
                                                        )</label>
                                                    <span class="material-icons">note_alt</span>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                                @foreach ($language?->live_values as $lang)
                                                    <div
                                                        class="form-floating form-floating__icon mb-30 d-none lang-form"
                                                        id="{{$lang['code']}}-form">
                                                        <input type="text" name="name[]" class="form-control"
                                                               placeholder="{{translate('zone_name')}}">
                                                        <label>{{translate('zone_name')}}
                                                            ({{strtoupper($lang['code'])}})</label>
                                                        <span class="material-icons">note_alt</span>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                @endforeach
                                            @else
                                                <div class="lang-form">
                                                    <div class="mb-30">
                                                        <div class="form-floating form-floating__icon">
                                                            <input type="text" class="form-control" name="name[]"
                                                                   placeholder="{{translate('zone_name')}} *"
                                                                   required value="{{old('name')}}">
                                                            <label>{{translate('zone_name')}} *</label>
                                                            <span class="material-icons">note_alt</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            @endif

                                            <div class="form-group mb-3 coordinates">
                                                <label class="input-label"
                                                       for="exampleFormControlInput1">{{translate('coordinates')}}
                                                    <span
                                                        class="input-label-secondary">{{translate('draw_your_zone_on_the_map')}}</span>
                                                </label>
                                                <textarea type="text" rows="8" name="coordinates" id="coordinates"
                                                          class="form-control" readonly></textarea>
                                            </div>

                                            <div class="map-warper dark-support rounded overflow-hidden">
                                                <input id="pac-input" class="controls rounded search_area"
                                                       title="{{translate('search_your_location_here')}}" type="text"
                                                       placeholder="{{translate('search_here')}}"/>
                                                <div class="map_canvas" id="map-canvas"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-20 mt-30">
                                                <button class="btn btn--secondary" type="reset"
                                                        id="reset_btn">{{translate('reset')}}</button>
                                                <button class="btn btn--primary"
                                                        type="submit">{{translate('submit')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div class="d-flex justify-content-end border-bottom mx-lg-4 mb-10">
                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Zones')}}:</span>
                            <span class="title-color">{{ $zones->total() }}</span>
                        </div>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @can('zone_export')
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                       href="{{route('admin.zone.download')}}?search={{$search}}">{{translate('excel')}}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('zone_name')}}</th>
                                        <th>{{translate('providers')}}</th>
                                        <th>{{translate('Category')}}</th>
                                        @can('zone_manage_status')
                                            <th>{{translate('status')}}</th>
                                        @endcan
                                        @canany(['zone_delete', 'zone_update'])
                                            <th>{{translate('action')}}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($zones as $key=>$zone)
                                        <tr>
                                            <td>{{$key+$zones->firstItem()}}</td>
                                            <td>{{$zone->name}}</td>
                                            <td>{{$zone->providers_count}}</td>
                                            <td>{{$zone->categories_count}}</td>
                                            @can('zone_manage_status')
                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input route-alert"
                                                               data-route="{{route('admin.zone.status-update',[$zone->id])}}"
                                                               data-message="{{translate('want_to_update_status')}}"
                                                               type="checkbox" {{$zone->is_active?'checked':''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            @endcan
                                            @canany(['zone_delete', 'zone_update'])
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @can('zone_update')
                                                            <a href="{{route('admin.zone.edit',[$zone->id])}}"
                                                               class="action-btn btn--light-primary demo_check">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        @endcan
                                                        @can('zone_delete')
                                                            <button type="button"
                                                                    data-id="delete-{{$zone->id}}"
                                                                    data-message="{{translate('want_to_delete_this_zone')}}?"
                                                                    class="action-btn btn--danger {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                                                    style="--size: 30px">
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            <form
                                                                action="{{route('admin.zone.delete',[$zone->id])}}"
                                                                method="post" id="delete-{{$zone->id}}"
                                                                class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endcan
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $zones->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/assets/admin-module/plugins/dataTables/dataTables.select.min.js')}}"></script>

    @php($api_key=(business_config('google_map', 'third_party'))->live_values)
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{$api_key['map_api_key_client']}}&libraries=drawing,places&v=3.45.8"></script>

    <script>
        "use strict";

        var map;
        var drawingManager;
        var lastpolygon = null;
        var polygons = [];

        auto_grow();

        function auto_grow() {
            let element = document.getElementById("coordinates");
            element.style.height = "5px";
            element.style.height = (element.scrollHeight) + "px";
        }

        function resetMap(controlDiv) {
            const controlUI = document.createElement("div");
            controlUI.style.backgroundColor = "#fff";
            controlUI.style.border = "2px solid #fff";
            controlUI.style.borderRadius = "3px";
            controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
            controlUI.style.cursor = "pointer";
            controlUI.style.marginTop = "8px";
            controlUI.style.marginBottom = "22px";
            controlUI.style.textAlign = "center";
            controlUI.title = "Reset map";
            controlDiv.appendChild(controlUI);
            const controlText = document.createElement("div");
            controlText.style.color = "rgb(25,25,25)";
            controlText.style.fontFamily = "Roboto,Arial,sans-serif";
            controlText.style.fontSize = "10px";
            controlText.style.lineHeight = "16px";
            controlText.style.paddingLeft = "2px";
            controlText.style.paddingRight = "2px";
            controlText.innerHTML = "X";
            controlUI.appendChild(controlText);
            // Setup the click event listeners: simply set the map to Chicago.
            controlUI.addEventListener("click", () => {
                lastpolygon.setMap(null);
                $('#coordinates').val('');
            });
        }

        @php($location = session('location'))

        function initialize() {
            var myLatlng = {
                lat: '{{$location['lat']}}',
                lng: '{{$location['lng']}}'
            };

            var myOptions = {
                zoom: 13,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
            }
            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
            drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: [google.maps.drawing.OverlayType.POLYGON]
                },
                polygonOptions: {
                    editable: true
                }
            });
            drawingManager.setMap(map);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        map.setCenter(pos);
                    });
            }

            google.maps.event.addListener(drawingManager, "overlaycomplete", function (event) {
                if (lastpolygon) {
                    lastpolygon.setMap(null);
                }
                $('#coordinates').val(event.overlay.getPath().getArray());
                lastpolygon = event.overlay;
                auto_grow();
            });

            const resetDiv = document.createElement("div");
            resetMap(resetDiv, lastpolygon);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(resetDiv);

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
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
                    const icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25),
                    };
                    // Create a marker for each place.
                    markers.push(
                        new google.maps.Marker({
                            map,
                            icon,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
        }

        google.maps.event.addDomListener(window, 'load', initialize);

        $('#reset_btn').click(function () {
            $('#name').val(null);

            lastpolygon.setMap(null);
            $('#coordinates').val(null);
        })

        function performValidation(event) {
            if (!lastpolygon) {
                event.preventDefault();
            }
        }

        $('#zone-form').submit(function (event) {
            performValidation(event);
        });

        $('#pac-input').keydown(function (event) {
            if (event.keyCode === 13) {
                performValidation(event);
            }
        });

        $(".lang_link").on('click', function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang-form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
        });
    </script>
@endpush
