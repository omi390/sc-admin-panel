@extends('adminmodule::layouts.master')

@section('title',translate('gallery_settings'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-md-flex_ align-items-center justify-content-between mb-2">
            <div class="row gy-2 align-items-center d-flex justify-content-between">
                <div class="col-sm-auto">
                    <h3 class="h3 m-0 text-capitalize">{{translate('file_manager')}}</h3>
                </div>

                <div class="col-sm-auto ml-auto">
                    @can('gallery_export')
                        <a href="{{route('admin.business-settings.download.public', ['storage' => $storage])}}" class="btn btn--secondary">
                            <span class="material-icons">download</span> {{translate('download')}}
                        </a>
                    @endcan
                    @can('gallery_add')
                        <button type="button" class="btn btn--primary modalTrigger" data-bs-toggle="modal"
                                data-bs-target="#exampleModal">
                            <span class="material-icons">add_circle</span> {{translate('Add New')}}
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="mb-4 mt-2">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 __gap-12px">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                        <!-- Nav -->
                        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                            <li class="nav-item">
                                <a class="nav-link {{ $storage == 'local' ? 'active' : '' }}"
                                   href="{{ route('admin.business-settings.get-gallery-setup', ['path' => 'cHVibGlj', 'storage' => 'local']) }}">
                                    {{ translate('local_storage') }}
                                </a>
                            </li>
                            @if(getDisk() == 's3')
                                <li class="nav-item">
                                    <a class="nav-link {{ $storage == 's3' ? 'active' : '' }}"
                                       href="{{ route('admin.business-settings.get-gallery-setup', ['path' => 'cHVibGlj', 'storage' => 's3']) }}">
                                        {{ translate('S3_bucket') }}
                                    </a>
                                </li>
                            @endif
                        </ul>

                        <!-- End Nav -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        @php
                            $pwd = explode('/',base64_decode($folderPath));
                            $awsUrl = config('filesystems.disks.s3.url');
                            $awsBucket = config('filesystems.disks.s3.bucket');
                        @endphp
                        <h5 class="card-title text-capitalize d-flex align-items-center gap-2">
                            <span class="card-header-icon">
                                <i class="tio-folder-opened-labeled"></i>
                            </span> {{end($pwd)}} <span class="badge badge-soft-dark text-dark rounded-pill fs-12"
                                                        id="itemCount">{{count($data)}}</span>
                        </h5>
                        <a class="btn btn-sm btn--primary" href="{{url()->previous()}}"><span class="material-icons">arrow_back</span> {{translate('back')}}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($data as $key=>$file)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    @if($file['type']=='folder')
                                        <a class="p-0 row text-capitalize"
                                           href="{{route('admin.business-settings.get-gallery-setup', [base64_encode($file['path']), 'storage' => $storage])}}">
                                            <div><img class=""
                                                      src="{{asset('public/assets/admin-module/img/folder.png')}}"
                                                      alt=""></div>
                                            <div><p>{{Str::limit($file['name'],10)}}</p></div>
                                        </a>
                                    @elseif($file['type']=='file')
                                        <div class="" data-bs-toggle="modal" data-bs-target="#imagemodal{{$key}}"
                                             title="{{$file['name']}}">
                                            <div class="gallary-card initial-25 mb-2">
                                                <img class="initial-26" width="150"
                                                     src="{{$storage == 's3'? rtrim($awsUrl, '/').'/'.ltrim($awsBucket.'/'.$file['path'], '/') : asset('storage/app/'.$file['path'])}}"
                                                     alt="{{$file['name']}}">
                                            </div>
                                            <p class="overflow-hidden">{{Str::limit($file['name'],10)}}</p>
                                        </div>
                                        <div class="modal fade" id="imagemodal{{$key}}" tabindex="-1" role="dialog"
                                             aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="myModalLabel">{{$file['name']}}</h4>
                                                        <button type="button" class="close btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                            <span
                                                                class="material-icons">close</span> {{translate('Close')}}
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img src="{{$storage == 's3'? rtrim($awsUrl, '/').'/'.ltrim($awsBucket.'/'.$file['path'], '/') : asset('storage/app/'.$file['path'])}}"
                                                             class="initial-27">
                                                    </div>
                                                    <div class="modal-footer">
                                                        @can('gallery_export')
                                                            <a class="btn btn-primary"
                                                               href="{{route('admin.business-settings.download-gallery-image', [base64_encode($file['path']), 'storage' => $storage])}}"><i
                                                                    class="tio-download"></i> {{translate('download')}}
                                                            </a>
                                                            <button class="btn btn-info  db-path"
                                                                    data-text="{{$file['db_path']}}"><i
                                                                    class="tio-copy"></i> {{ translate('Copy path') }}
                                                            </button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="indicator"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{translate('upload file')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{route('admin.business-settings.upload-gallery-image')}}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="text" name="path" value="{{base64_decode($folderPath)}}" hidden>
                            <input type="text" name="disk" value = "{{$storage}}" hidden>

                            <div class="form-group mb-4">
                                <div class="custom-file">
                                    <label class="mb-2" for="customFileUpload">{{translate('choose images')}}</label>
                                    <input type="file" name="images[]" id="customFileUpload" class="form-control"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" multiple>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="custom-file">
                                    <label class="mb-2" id="zipFileLabel"
                                           for="customZipFileUpload">{{translate('upload_zip_file')}}</label>
                                    <input type="file" name="file" id="customZipFileUpload" class="form-control"
                                           accept=".zip">
                                </div>
                            </div>

                            <div class="row mb-3" id="files"></div>
                            <div class="d-flex justify-content-end">
                                <input class="btn btn--primary" type="submit" value="{{translate('upload')}}">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        function readURL(input) {
            $('#files').html("");
            for (var i = 0; i < input.files.length; i++) {
                if (input.files && input.files[i]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#files').append('<div class="col-md-2 col-sm-4 m-1"><img class="initial-28" id="viewer" src="' + e.target.result + '"/></div>');
                    }
                    reader.readAsDataURL(input.files[i]);
                }
            }

        }

        $("#customFileUpload").change(function () {
            readURL(this);
        });

        $('#customZipFileUpload').change(function (e) {
            var fileName = e.target.files[0].name;
            $('#zipFileLabel').html(fileName);
        });

        $('.db-path').on('click', function () {
            let text = $(this).data('text');
            copy_test(text)
        });

        function copy_test(copyText) {
            navigator.clipboard.writeText(copyText);

            toastr.success('{{ translate('File path copied successfully!') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        }

    </script>
@endpush
