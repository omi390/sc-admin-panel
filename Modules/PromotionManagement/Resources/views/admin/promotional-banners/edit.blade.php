@extends('adminmodule::layouts.master')

@section('title',translate('promotional_banner_update'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('promotional_banner_update')}}</h2>
                    </div>
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form action="{{route('admin.banner.update',[$banner->id])}}" method="POST">
                                @method('PUT')
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6 mb-4 mb-lg-0">
                                        <div class="mb-30">
                                            <label class="form-label">{{translate('main_category')}} *</label>
                                            <select class="js-select theme-input-style w-100" name="main_category_id"
                                                    id="main_category_id" required>
                                                <option value="" disabled>---{{translate('select_main_category')}}---</option>
                                                @foreach($mainCategories as $mainCat)
                                                    <option value="{{$mainCat->id}}" {{$mainCat->id==$banner->main_category_id?'selected':''}}>{{$mainCat->CategoryCode ?? $mainCat->title}} (ID: {{$mainCat->id}})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-30">
                                            <label class="form-label">{{translate('zone')}} *</label>
                                            <select class="js-select theme-input-style w-100" name="zone_id" required>
                                                <option value="" disabled>---{{translate('select_zone')}}---</option>
                                                @foreach($zones as $zone)
                                                    <option value="{{$zone->id}}" {{$zone->id==$banner->zone_id?'selected':''}}>{{$zone->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="text" class="form-control" name="banner_title"
                                                   value="{{$banner->banner_title}}"
                                                   placeholder="{{translate('title')}} *"
                                                   required="">
                                            <label>{{translate('title')}} *</label>
                                            <span class="material-icons">title</span>
                                        </div>

                                        <div class="mb-3">{{translate('resource_type')}}</div>
                                        <div class="d-flex flex-wrap align-items-center gap-4 mb-30">
                                            <div class="custom-radio">
                                                <input type="radio" id="category" name="resource_type" value="category"
                                                    {{$banner->resource_type=='category'?'checked':''}}>
                                                <label for="category">{{translate('category_wise')}}</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" id="service" name="resource_type" value="service"
                                                    {{$banner->resource_type=='service'?'checked':''}}>
                                                <label for="service">{{translate('service_wise')}}</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" id="redirect_link" name="resource_type"
                                                       value="link" {{$banner->resource_type=='link'?'checked':''}}>
                                                <label for="redirect_link">{{translate('redirect_link')}}</label>
                                            </div>
                                        </div>

                                        <div class="mb-30" id="category_selector"
                                             style="display: {{$banner->resource_type=='category'?'block':'none'}}">
                                            <label class="form-label">{{translate('sub_category')}}</label>
                                            <select class="js-select theme-input-style w-100" name="category_id" id="category_id">
                                                <option value="">{{translate('select_sub_category')}}</option>
                                                @foreach($subCategories as $subCat)
                                                    <option value="{{$subCat->id}}" {{$subCat->id==$banner->resource_id?'selected':''}}>
                                                        {{$subCat->name}} ({{$subCat->parent?->name ?? '-'}})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-30" id="service_selector"
                                             style="display: {{$banner->resource_type=='service'?'block':'none'}}">
                                            <select class="js-select theme-input-style w-100" name="service_id">
                                                <option value="" selected disabled>---{{translate('Select_Service')}}
                                                    ---
                                                </option>
                                                @foreach($services as $service)
                                                    <option
                                                        value="{{$service->id}}" {{$service->id==$banner->resource_id?'selected':''}}>
                                                        {{$service->name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-floating form-floating__icon mb-30"
                                             style="display: {{$banner->resource_type=='link'?'block':'none'}}"
                                             id="link_selector">
                                            <input type="url" class="form-control" name="redirect_link"
                                                   placeholder="{{translate('redirect_link')}}"
                                                   value="{{$banner->redirect_link}}">
                                            <label>{{translate('redirect_link')}}</label>
                                            <span class="material-icons">link</span>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-floating form-floating__icon mb-30">
                                            <input type="url" class="form-control" name="banner_image"
                                                   id="banner_image_url"
                                                   placeholder="{{translate('banner_image_url')}} *"
                                                   value="{{ $banner->banner_image }}"
                                                   required="">
                                            <label>{{translate('banner_image_url')}} *</label>
                                            <span class="material-icons">image</span>
                                        </div>
                                        <div class="mb-30">
                                            <p class="title-color mb-2">{{translate('image_preview')}}</p>
                                            <div class="upload-file__img upload-file__img_banner border rounded p-2" style="min-height: 120px;">
                                                <img id="banner_image_preview"
                                                     src="{{ $banner->banner_image_full_path }}"
                                                     alt="{{ translate('banner') }}"
                                                     class="img-fluid" style="max-height: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-20 mt-30">
                                            <button class="btn btn--secondary"
                                                    type="reset">{{translate('reset')}}</button>
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
    <script src="{{asset('assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
    <script>
        "use Strict";

        $('#category').on('click', function () {
            $('#category_selector').show();
            $('#service_selector').hide();
            $('#link_selector').hide();
        });

        $('#service').on('click', function () {
            $('#category_selector').hide();
            $('#service_selector').show();
            $('#link_selector').hide();
        });

        $('#redirect_link').on('click', function () {
            $('#category_selector').hide();
            $('#service_selector').hide();
            $('#link_selector').show();
        });

        $(document).ready(function () {
            $('.js-select').select2();
        });

        // Banner image URL preview
        $('#banner_image_url').on('input', function () {
            var url = $(this).val();
            var $preview = $('#banner_image_preview');
            if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                $preview.attr('src', url).on('error', function () {
                    $(this).attr('src', '{{ asset("assets/admin-module/img/media/banner-upload-file.png") }}');
                });
            } else {
                $preview.attr('src', '{{ asset("assets/admin-module/img/media/banner-upload-file.png") }}');
            }
        });
    </script>
@endpush
