@extends('adminmodule::layouts.master')

@section('title',translate('profile_update'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update_Profile')}}</h2>
                    </div>

                    <form action="{{ route('admin.tendors.update', $tendor->id) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <input name="name" class="form-control" value="{{ $tendor->name }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="type">Type</label>
                            <input name="type" class="form-control" value="{{ $tendor->type }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="location">Location</label>
                            <input name="location" class="form-control" value="{{ $tendor->location }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="duration">Duration</label>
                            <input name="duration" class="form-control" value="{{ $tendor->duration }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="material_type">Material Type</label>
                            <input name="material_type" class="form-control" value="{{ $tendor->material_type }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="category_type">Category Type</label>
                            <input name="category_type" class="form-control" value="{{ $tendor->category_type }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="emd">EMD</label>
                            <input name="emd" class="form-control" value="{{ $tendor->emd }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="fee">Fee</label>
                            <input name="fee" class="form-control" value="{{ $tendor->fee }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tendor_price">Tendor Price</label>
                            <input name="tendor_price" class="form-control" value="{{ $tendor->tendor_price }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tendor_location">Tendor Location</label>
                            <input name="tendor_location" class="form-control" value="{{ $tendor->tendor_location }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="closing_date">Closing Date</label>
                            <input type="date" name="closing_date" class="form-control" value="{{ $tendor->closing_date }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tendor_closing_date">Tendor Closing Date</label>
                            <input type="date" name="tendor_closing_date" class="form-control" value="{{ $tendor->tendor_closing_date }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tendor_organization">Tendor Organization</label>
                            <input name="tendor_organization" class="form-control" value="{{ $tendor->tendor_organization }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="tendor_code">Tendor Code</label>
                            <input name="tendor_code" class="form-control" value="{{ $tendor->tendor_code }}">
                        </div>

                        <div class="form-group mb-4">
                            <label for="desc">Description</label>
                            <textarea name="desc" class="form-control" rows="4">{{ $tendor->desc }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">Update</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
