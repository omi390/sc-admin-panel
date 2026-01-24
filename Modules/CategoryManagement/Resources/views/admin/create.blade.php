@extends('adminmodule::layouts.master')

@section('title',translate('category_setup'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/select2/select2.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/jquery.dataTables.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/dataTables/select.dataTables.min.css')}}"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('category_setup')}}</h2>
                    </div>

                    @can('category_add')
                        <div class="card category-setup mb-30">
                            <div class="card-body p-30">
                                <form action="{{route('admin.category.store')}}" method="post"
                                      enctype="multipart/form-data">
                                    @csrf
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
                                    <div class="row">
                                        <div class="col-lg-8 mb-5 mb-lg-0">
                                            <div class="d-flex flex-column">
                                                @if ($language)
                                                    <div class="form-floating form-floating__icon mb-30 lang-form"
                                                         id="default-form">
                                                        <input type="text" name="name[]" class="form-control" required
                                                               placeholder="{{translate('category_name')}}">
                                                        <label>{{translate('category_name')}}
                                                            ({{ translate('default') }}
                                                            )</label>
                                                        <span class="material-icons">subtitles</span>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    @foreach ($language?->live_values as $lang)
                                                        <div
                                                            class="form-floating form-floating__icon mb-30 d-none lang-form"
                                                            id="{{$lang['code']}}-form">
                                                            <input type="text" name="name[]" class="form-control"
                                                                   placeholder="{{translate('category_name')}}">
                                                            <label>{{translate('category_name')}}
                                                                ({{strtoupper($lang['code'])}})</label>
                                                            <span class="material-icons">subtitles</span>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                    @endforeach
                                                @else
                                                    <div class="form-floating form-floating__icon mb-30">
                                                        <input type="text" name="name[]" class="form-control"
                                                               placeholder="{{translate('category_name')}}" required>
                                                        <label>{{translate('category_name')}}</label>
                                                        <span class="material-icons">subtitles</span>
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif

                                                <select class="select-zone theme-input-style w-100" name="zone_ids[]"
                                                        multiple="multiple" id="zone_selector__select">
                                                    <option value="all">{{translate('Select All')}}</option>
                                                    @foreach($zones as $zone)
                                                        <option value="{{$zone['id']}}">{{$zone->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="d-flex  gap-3 gap-xl-5">
                                                <p class="opacity-75 max-w220">{{translate('image_format_-_jpg,_png,_jpeg,_gif_image
                                                size_-_
                                                maximum_size_2_MB_Image_Ratio_-_1:1')}}</p>
                                                <div class="d-flex align-items-center flex-column">
                                                    <div class="upload-file">
                                                        <input type="file" class="upload-file__input" name="image"
                                                               accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*">
                                                        <div class="upload-file__img">
                                                            <img
                                                                src="{{asset('public/assets/admin-module/img/media/upload-file.png')}}"
                                                                alt="{{translate('image')}}">
                                                        </div>
                                                        <span class="upload-file__edit">
                                                        <span class="material-icons">edit</span>
                                                    </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-20 mt-30">
                                                <button class="btn btn--secondary"
                                                        type="reset">{{translate('reset')}}</button>
                                                <button class="btn btn--primary" type="submit">{{translate('submit')}}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{url()->current()}}?status=all">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='active'?'active':''}}"
                                   href="{{url()->current()}}?status=active">
                                    {{translate('active')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='inactive'?'active':''}}"
                                   href="{{url()->current()}}?status=inactive">
                                    {{translate('inactive')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Categories')}}:</span>
                            <span class="title-color">{{$categories->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="{{url()->current()}}?status={{$status}}"
                                              class="search-form search-form_style-two"
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
                                            <button type="submit"
                                                    class="btn btn--primary">{{translate('search')}}</button>
                                        </form>

                                        @can('category_export')
                                            <div class="d-flex flex-wrap align-items-center gap-3">
                                                <div class="dropdown">
                                                    <button type="button"
                                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                        <span class="material-icons">file_download</span> download
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                        <li><a class="dropdown-item"
                                                               href="{{route('admin.category.download')}}?search={{$search}}">{{translate('excel')}}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead class="align-middle">
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('category_name')}}</th>
                                                <th>{{translate('sub_category_count')}}</th>
                                                <th>{{translate('zone_count')}}</th>
                                                @can('category_manage_status')
                                                    <th>{{translate('status')}}</th>
                                                @endcan
                                                @can('category_manage_status')
                                                    <th>{{translate('Is_Featured')}}</th>
                                                @endcan
                                                @canany(['category_delete', 'category_update'])
                                                    <th>{{translate('action')}}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($categories as $key=>$category)
                                                <tr>
                                                    <td>{{$categories->firstitem()+$key}}</td>
                                                    <td>{{$category->name}}</td>
                                                    <td>{{$category->children_count}}</td>
                                                    <td>{{$category->zones_count}}</td>
                                                    @can('category_manage_status')
                                                        <td>
                                                            <label class="switcher" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input status-update"
                                                                       type="checkbox"
                                                                       {{$category->is_active?'checked':''}} data-status="{{$category->id}}">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @can('category_manage_status')
                                                        <td>
                                                            <label class="switcher">
                                                                <input class="switcher_input feature-update"
                                                                       type="checkbox"
                                                                       {{$category->is_featured?'checked':''}} data-featured="{{$category->id}}">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['category_delete', 'category_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                @can('category_update')
                                                                    <a href="{{route('admin.category.edit',[$category->id])}}"
                                                                       class="action-btn btn--light-primary demo_check"
                                                                       style="--size: 30px">
                                                                        <span class="material-icons">edit</span>
                                                                    </a>
                                                                @endcan
                                                                @can('category_delete')
                                                                    <button type="button"
                                                                            data-id="delete-{{$category->id}}"
                                                                            data-message="{{translate('want_to_delete_this_category')}}?"
                                                                            class="action-btn btn--danger {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                                                            style="--size: 30px">
                                                                    <span
                                                                        class="material-symbols-outlined">delete</span>
                                                                    </button>
                                                                    <form
                                                                        action="{{route('admin.category.delete',[$category->id])}}"
                                                                        method="post" id="delete-{{$category->id}}"
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
                                        {!! $categories->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script src="{{asset('public/assets/category-module')}}/js/category/create.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>

    <script>
        "use strict"

        $('#zone_selector__select').on('change', function () {
            var selectedValues = $(this).val();
            if (selectedValues !== null && selectedValues.includes('all')) {
                $(this).find('option').not(':disabled').prop('selected', 'selected');
                $(this).find('option[value="all"]').prop('selected', false);
            }
        });

        $('.status-update').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{route('admin.category.status-update',['id' => ':itemId'])}}';
            route = route.replace(':itemId', itemId);
            route_alert(route, '{{ translate('want_to_update_status') }}');
        })

        $('.feature-update').on('click', function () {
            let itemId = $(this).data('featured');
            let route = '{{route('admin.category.featured-update',['id' => ':itemId'])}}';
            route = route.replace(':itemId', itemId);
            route_alert(route, '{{ translate('want_to_update_status') }}');
        })
    </script>

@endpush
