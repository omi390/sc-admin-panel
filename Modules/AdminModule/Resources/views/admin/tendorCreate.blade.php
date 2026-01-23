@extends('adminmodule::layouts.master')

@section('title',translate('profile_update'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
          <div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Add Tendor</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.tendors.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input name="name" class="form-control" placeholder="Enter name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input name="type" class="form-control" placeholder="Enter type">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input name="location" class="form-control" placeholder="Enter location">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="duration" class="form-label">Duration</label>
                        <input name="duration" class="form-control" placeholder="Enter duration">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="material_type" class="form-label">Material Type</label>
                        <input name="material_type" class="form-control" placeholder="Enter material type">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category_type" class="form-label">Category Type</label>
                        <input name="category_type" class="form-control" placeholder="Enter category type">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="emd" class="form-label">EMD</label>
                        <input name="emd" class="form-control" placeholder="Enter EMD">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fee" class="form-label">Fee</label>
                        <input name="fee" class="form-control" placeholder="Enter fee">
                    </div>

                    <div class="col-12 mb-4">
                        <label for="desc" class="form-label">Description</label>
                        <textarea name="desc" class="form-control" rows="4" placeholder="Enter description"></textarea>
                    </div>
                    
                        <div class="col-md-6 mb-3">
                        <label for="tendor_price" class="form-label">Tendor Price</label>
                        <input name="tendor_price" class="form-control" placeholder="Enter tendor price">
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="tendor_location" class="form-label">Tendor Location</label>
                        <input name="tendor_location" class="form-control" placeholder="Enter tendor location">
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="closing_date" class="form-label">Closing Date</label>
                        <input type="date" name="closing_date" class="form-control">
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="tendor_closing_date" class="form-label">Tendor Closing Date</label>
                        <input type="date" name="tendor_closing_date" class="form-control">
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="tendor_organization" class="form-label">Tendor Organization</label>
                        <input name="tendor_organization" class="form-control" placeholder="Enter organization">
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="tendor_code" class="form-label">Tendor Code</label>
                        <input name="tendor_code" class="form-control" placeholder="Enter tendor code">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Save Tendor</button>
            </form>
        </div>
    </div>
        </div>
    </div>
@endsection

