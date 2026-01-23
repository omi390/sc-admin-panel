@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='overview'?'active':''}}"
                           href="{{url()->current()}}?web_page=overview">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscribed_services'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscribed_services">{{translate('Subscribed_Services')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bookings'?'active':''}}"
                           href="{{url()->current()}}?web_page=bookings">{{translate('Bookings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='serviceman_list'?'active':''}}"
                           href="{{url()->current()}}?web_page=serviceman_list">{{translate('Service_Man_List')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='settings'?'active':''}}"
                           href="{{url()->current()}}?web_page=settings">{{translate('Settings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bank_information'?'active':''}}"
                           href="{{url()->current()}}?web_page=bank_information">{{translate('Bank_Information')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='reviews'?'active':''}}"
                           href="{{url()->current()}}?web_page=reviews">{{translate('Reviews')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscription'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscription&provider_id={{ request()->id }}">{{translate('Business Plan')}}</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="subscribed-tab-pane">
                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=all">{{translate('All')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='subscribed'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=subscribed">{{translate('Subscribed')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='unsubscribed'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=unsubscribed">{{translate('Unsubscribed')}}</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Sub_Categories')}}:</span>
                            <span class="title-color">{{$subCategories->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form
                                            action="{{url()->current()}}?web_page=subscribed_services&status={{$status}}"
                                            class="search-form search-form_style-two"
                                            method="POST">
                                            @csrf
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$search??''}}" name="search"
                                                       placeholder="{{translate('search_here')}}">
                                            </div>
                                            <button type="submit" class="btn btn--primary">
                                                {{translate('search')}}
                                            </button>
                                        </form>
                                        @can('provider_manage_status')
                                        <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#addSubCategoryModal">
                                            <span class="material-icons">add</span>
                                            {{translate('Add_Sub_Category')}}
                                        </button>
                                        @endcan
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-center align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{translate('Sub_Category_Name')}}</th>
                                                <th>{{translate('Services')}}</th>
                                                <th>{{translate('Subscribe_/_Unsubscribe')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($subCategories as $sub_category)
                                                <tr>
                                                    <td>
                                                        <div data-bs-toggle="modal"
                                                             data-bs-target="#showServiceModal">{{Str::limit($sub_category->sub_category?$sub_category->sub_category->name:'', 30)}}</div>
                                                    </td>
                                                    <td>{{$sub_category->sub_category?$sub_category->sub_category->services_count:0}}</td>
                                                    <td>
                                                        @can('provider_manage_status')
                                                            <label class="switcher" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input route-alert"
                                                                       data-route="{{route('admin.provider.sub_category.update_subscription',[$sub_category->id])}}"
                                                                       data-message="{{translate('want_to_update_status')}}"
                                                                       type="checkbox" {{$sub_category->is_subscribed == 1 ? 'checked' : ''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $subCategories->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Sub Category Modal -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1" aria-labelledby="addSubCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header px-4 pt-4 border-0 pb-1">
                    <h3 class="text-capitalize">{{translate('Add_Sub_Category')}}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addSubCategoryForm" action="{{route('admin.provider.sub_category.add_subscription', request()->id)}}" method="POST">
                    @csrf
                    <div class="modal-body px-4">
                        <div class="mb-30">
                            <label class="form-label mb-2">{{translate('Select_Sub_Categories')}} *</label>
                            <select class="js-select theme-input-style w-100" name="sub_category_ids[]" id="sub_category_ids" multiple required>
                                <option value="">{{translate('Select_Sub_Categories')}}</option>
                            </select>
                            <div class="form-text">{{translate('Select_one_or_more_sub_categories_to_subscribe')}}</div>
                        </div>
                    </div>
                    <div class="modal-footer px-4 pb-4 pt-2 border-0">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('Add')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            const providerId = '{{request()->id}}';
            
            // Initialize select2
            $('#sub_category_ids').select2({
                placeholder: '{{translate("Select_Sub_Categories")}}',
                allowClear: true,
                width: '100%'
            });

            // Load available subcategories when modal is opened
            $('#addSubCategoryModal').on('show.bs.modal', function () {
                loadAvailableSubCategories(providerId);
            });

            // Handle form submission
            $('#addSubCategoryForm').on('submit', function (e) {
                e.preventDefault();
                
                const form = $(this);
                const formData = form.serialize();
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> {{translate("Adding")}}...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.response_code === 'default_store_200' || response.response_code === 'default_200') {
                            toastr.success(response.message || '{{translate("Sub_categories_added_successfully")}}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            $('#addSubCategoryModal').modal('hide');
                            // Reload the page to show updated list
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message || '{{translate("Failed_to_add_sub_categories")}}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = '{{translate("Failed_to_add_sub_categories")}}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        toastr.error(errorMessage, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function loadAvailableSubCategories(providerId) {
                $.ajax({
                    url: '{{route("admin.provider.sub_category.available", ":id")}}'.replace(':id', providerId),
                    type: 'GET',
                    success: function (response) {
                        if (response.response_code === 'default_200' && response.content) {
                            $('#sub_category_ids').empty();
                            $('#sub_category_ids').append('<option value="">{{translate("Select_Sub_Categories")}}</option>');
                            
                            const subCategories = Array.isArray(response.content) ? response.content : [];
                            subCategories.forEach(function(subCategory) {
                                $('#sub_category_ids').append(
                                    $('<option></option>')
                                        .attr('value', subCategory.id)
                                        .text(subCategory.name + (subCategory.parent ? ' (' + subCategory.parent.name + ')' : ''))
                                );
                            });
                            
                            $('#sub_category_ids').trigger('change');
                        } else {
                            toastr.warning(response.message || '{{translate("No_available_sub_categories")}}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    },
                    error: function () {
                        toastr.error('{{translate("Failed_to_load_sub_categories")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                });
            }
        });
    </script>
@endpush
