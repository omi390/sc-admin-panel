@extends('adminmodule::layouts.master')

@section('title', translate('profile_update'))

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="page-title-wrap mb-3 d-flex justify-content-between align-items-center">
                    <h2 class="page-title">Tabs Management</h2>

                    <!-- Create Button -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTabModal">
                        Create New Entry
                    </button>
                </div>

                <!-- TABLE START -->
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($tabs as $tab)
                                    <tr>
                                        <td>{{ $tab->title }}</td>
                                        <td>{{ $tab->category_name }}</td>
        <td><a href="{{ route('admin.tabsDelete', $tab->id) }}" class="btn btn-danger">Delete</a></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- TABLE END -->

            </div>
        </div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createTabModal" tabindex="-1" aria-labelledby="createTabModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="/admin/tabs/save" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="createTabModalLabel">Create New Tab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Tabs</label>
                        <select name="tab_id" class="form-control">
                            @foreach($tabsList as $tab)
                                <option value="{{$tab->id}}">{{$tab->title}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category {{count($cats)}}</label>
                        <select name="category_id" class="form-control">
                            @foreach($cats as $cat)
                                <option value="{{$cat['id']}}"> {{$cat['name']}}</option>
                            @endforeach
                        </select>
                    </div>

                    

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>

            </form>

        </div>
    </div>
</div>
<!-- END MODAL -->

@endsection
