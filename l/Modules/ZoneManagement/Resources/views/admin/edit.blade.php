@extends('adminmodule::layouts.master')

@section('title',translate('zone_edit'))

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
                        <h2 class="page-title">{{translate('zone_update')}}</h2>
                    </div>

                    <div class="card zone-setup-instructions mb-30">
                        <div class="card-body p-30">
                            <form action="{{route('admin.zone.update',[$zone->id])}}" enctype="multipart/form-data"
                                  method="POST">
                                @csrf
                                @method('PUT')
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
                                                    <p>{{translate('click_this_icon_to_start_pin_points_in_the_map_and_connect_them_
                                                        to_draw_a_
                                                        zone_._Minimum_3_points_required')}}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="map-img mt-4">
                                                <img src="{{asset('public/assets/admin-module/img/instructions.gif')}}"
                                                     alt="">
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
                                            <div class="form-floating form-floating__icon mb-30 lang-form" id="default-form">
                                                <input type="text" name="name[]" class="form-control"
                                                       placeholder="{{translate('zone_name')}}"
                                                       value="{{$zone?->getRawOriginal('name')}}" required>
                                                <label>{{translate('zone_name')}} ({{ translate('default') }})</label>
                                                <span class="material-icons">note_alt</span>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            @foreach ($language?->live_values as $lang)
                                                    <?php
                                                    if (count($zone['translations'])) {
                                                        $translate = [];
                                                        foreach ($zone['translations'] as $t) {
                                                            if ($t->locale == $lang['code'] && $t->key == "zone_name") {
                                                                $translate[$lang['code']]['zone_name'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                <div class="form-floating form-floating__icon mb-30 d-none lang-form"
                                                     id="{{$lang['code']}}-form">
                                                    <input type="text" name="name[]" class="form-control"
                                                           placeholder="{{translate('zone_name')}}"
                                                           value="{{$translate[$lang['code']]['zone_name']??''}}">
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
                                                               required value="{{$zone->name}}">
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
                                                      class="form-control" readonly>@foreach($zone->coordinates[0]->toArray()['coordinates'] as $key=>$coords)
                                                        <?php if (count($zone->coordinates[0]->toArray()['coordinates']) != $key + 1) {
                                                        if ($key != 0) echo(','); ?>({{$coords[1]}}, {{$coords[0]}}
                                                    )<?php } ?>
                                                @endforeach</textarea>
                                        </div>

                                        <div class="map-warper overflow-hidden map_area">
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
                                                    type="submit">{{translate('update')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    @php($api_key=(business_config('google_map', 'third_party'))->live_values)
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{$api_key['map_api_key_client']}}&libraries=drawing,places&v=3.45.8"></script>

    <script>
        "use strict";
        auto_grow();

        function auto_grow() {
            let element = document.getElementById("coordinates");
            element.style.height = "5px";
            element.style.height = (element.scrollHeight) + "px";
        }

        let map; // Global declaration of the map
        let lat_longs = new Array();
        let drawingManager;
        let lastpolygon = null;
        let bounds = new google.maps.LatLngBounds();
        let polygons = [];


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
            controlUI.addEventListener("click", () => {
                lastpolygon.setMap(null);
                $('#coordinates').val('');

            });
        }

        function initialize() {
            let myLatlng = new google.maps.LatLng({{trim(explode(' ',$zone->center)[1], 'POINT()')}}, {{trim(explode(' ',$zone->center)[0], 'POINT()')}});
            let myOptions = {
                zoom: 13,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

            const polygonCoords = [

                    @foreach($area['coordinates'] as $coords)
                {
                    lat: {{$coords[1]}}, lng: {{$coords[0]}}
                },
                @endforeach
            ];

            var zonePolygon = new google.maps.Polygon({
                paths: polygonCoords,
                strokeColor: "#050df2",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillOpacity: 0,
            });

            zonePolygon.setMap(map);

            zonePolygon.getPaths().forEach(function (path) {
                path.forEach(function (latlng) {
                    bounds.extend(latlng);
                    map.fitBounds(bounds);
                });
            });


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

            google.maps.event.addListener(drawingManager, "overlaycomplete", function (event) {
                var newShape = event.overlay;
                newShape.type = event.type;
            });

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
                    const icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25),
                    };
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

        function set_all_zones() {
            $.get({
                url: '{{route('admin.zone.get-active-zones',[$zone->id])}}',
                dataType: 'json',
                success: function (data) {

                    for (var i = 0; i < data.length; i++) {
                        polygons.push(new google.maps.Polygon({
                            paths: data[i],
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.1,
                        }));
                        polygons[i].setMap(map);
                    }

                },
            });
        }

        $(document).on('ready', function () {
            set_all_zones();
        });

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

        $('form').submit(function(event) {
            performValidation(event);
        });

        $('#pac-input').keydown(function(event) {
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
            console.log(lang);
            $("#" + lang + "-form").removeClass('d-none');
        });
    </script>
@endpush
