@extends('adminmodule::layouts.master')

@section('title',translate('create_offline_payment'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('3rd_party')}}</h2>
                    </div>

                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            @include('businesssettingsmodule::admin.partials.third-party-partial')
                        </ul>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3 mt-5">
                        <ul class="nav nav--tabs nav--tabs__style2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{$type=='digital_payment'?'active':''}}"
                                   href="{{url('admin/configuration/get-third-party-config')}}?web_page=payment_config&type=digital_payment">{{translate('Digital Payment Gateways')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$type=='offline_payment'?'active':''}}"
                                   href="{{route('admin.configuration.offline-payment.list')}}?web_page=payment_config&type=offline_payment">{{translate('Offline Payment')}}</a>
                            </li>
                        </ul>
                    </div>
                    <form action="{{route('admin.configuration.offline-payment.store')}}" method="POST">
                        @csrf

                        <div class="d-flex justify-content-end mb-3 mt-3">
                            <div class="d-flex gap-2 justify-content-end text-primary fw-bold"
                                 id="bkashInfoModalButton">
                                Section View <i class="material-icons" data-bs-toggle="tooltip"
                                                title="{{translate('Admin needs to add the payment information for any offline payment, which customers will use to pay.')}}">info</i>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex align-items-center flex-wrap justify-content-between gap-2">
                                <h5 class="page-title">{{translate('payment_information')}}</h5>
                                <button class="btn btn-outline--primary" id="add-more-field-payment">
                                    <span class="material-icons">add</span> {{translate('Add_new_field')}}
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row gy-3">
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="method_name" id="method_name"
                                                   placeholder="Select method name" value="" required>
                                            <label for="method_name">{{translate('payment_method_name')}} *</label>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-3" id="custom-field-section-payment"></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-3 mt-4">
                            <div class="d-flex gap-2 justify-content-end text-primary fw-bold"
                                 id="paymentInfoModalButton">
                                Section View <i class="material-icons" data-bs-toggle="tooltip"
                                                title="{{translate('Admin needs to set the required customer information, which needs to be provided to the customers before placing a booking through offline payment')}}">info</i>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <h5 class="page-title">{{translate('Required Information from Customer')}}</h5>
                                <button class="btn btn-outline--primary" id="add-more-field-customer">
                                    <span class="material-icons">add</span> {{translate('Add_new_field')}}
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row gy-3">
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-floating">
                                            <textarea class="form-control" name="payment_note" id="payment_note"
                                                      placeholder="Select Payment Note" disabled></textarea>
                                            <label for="payment_note">{{translate('payment_note')}} *</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex flex-column gap-3" id="custom-field-section-customer"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end my-3">
                            <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary demo_check">{{translate('Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sectionViewModal" tabindex="-1" aria-labelledby="sectionViewModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center flex-column gap-3 text-center">
                        <h3>{{translate('Offline Payment')}}</h3>
                        <img width="100" src="{{asset('public/assets/admin-module/img/offline_payment.png')}}" alt="">
                        <p class="text-muted">{{translate('This view is from the user app.')}} <br
                                class="d-none d-sm-block"> {{translate('This is how customer will see in the app')}}</p>
                    </div>

                    <div class="rounded p-4 mt-3" id="offline_payment_top_part">
                        <div class="d-flex justify-content-between gap-2 mb-3">
                            <h4 id="payment_modal_method_name"><span></span></h4>
                            <div class="text-primary d-flex align-items-center gap-2">
                                {{translate('Pay on this account')}}
                                <span class="material-icons">check_circle</span>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2" id="methodNameDisplay">

                        </div>
                        <div class="d-flex flex-column gap-2" id="displayDataDiv">

                        </div>
                    </div>

                    <div class="rounded p-4 mt-3 mt-4" id="offline_payment_bottom_part">
                        <h2 class="text-center mb-4">{{translate('Amount')}} : xxx</h2>

                        <h4 class="mb-3">{{translate('Payment Info')}}</h4>
                        <div class="d-flex flex-column gap-3 mb-3" id="customer-info-display-div">

                        </div>
                        <div class="d-flex flex-column gap-3">
                    <textarea name="payment_note" id="payment_note" class="form-control"
                              readonly rows="10" placeholder="Note"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-3">
                        <button type="button" class="btn btn--secondary">{{translate('Close')}}</button>
                        <button type="button" class="btn btn--primary">{{translate('Submit')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')

    <script>
        "use strict"

        function openModal(contentArgument) {
            if (contentArgument === "bkashInfo") {
                $("#sectionViewModal #offline_payment_top_part").addClass("active");
                $("#sectionViewModal #offline_payment_bottom_part").removeClass("active");

                let methodName = $('#method_name').val();

                if (methodName !== '') {
                    $('#payment_modal_method_name').text(methodName + ' ' + 'Info');
                }

                function extractPaymentData() {
                    let data = [];

                    $('.field-row-payment').each(function (index) {
                        console.log('modal')
                        let title = $(this).find('input[name="title[]"]').val();
                        let dataValue = $(this).find('input[name="data[]"]').val();
                        data.push({title: title, data: dataValue});
                    });

                    return data;
                }

                let extractedData = extractPaymentData();


                function displayPaymentData() {
                    let displayDiv = $('#displayDataDiv');
                    let methodNameDisplay = $('#methodNameDisplay');
                    methodNameDisplay.empty();
                    displayDiv.empty();

                    let paymentElement = $('<span>').text('Payment Method');
                    let payementDataElement = $('<span>').html(methodName);

                    let dataRow = $('<div>').addClass('d-flex gap-3 align-items-center mb-2');
                    dataRow.append(paymentElement).append($('<span>').text(':')).append(payementDataElement);


                    methodNameDisplay.append(dataRow);

                    extractedData.forEach(function (item) {
                        let titleElement = $('<span>').text(item.title);
                        let dataElement = $('<span>').html(item.data);

                        let dataRow = $('<div>').addClass('d-flex gap-3 align-items-center');

                        if (item.title !== '') {
                            dataRow.append(titleElement).append($('<span>').text(':')).append(dataElement);
                            displayDiv.append(dataRow);
                        }

                    });
                }

                displayPaymentData();

                //customer info
                function extractCustomerData() {
                    let data = [];

                    $('.field-row-customer').each(function (index) {
                        let fieldName = $(this).find('input[name="field_name[' + index + ']"]').val();
                        let placeholder = $(this).find('input[name="placeholder[' + index + ']"]').val();
                        let isRequired = $(this).find('input[name="is_required[' + index + ']"]').prop('checked');
                        data.push({fieldName: fieldName, placeholder: placeholder, isRequired: isRequired});
                    });

                    return data;
                }

                let extractedCustomerData = extractCustomerData();
                $('#customer-info-display-div').empty();

                $.each(extractedCustomerData, function (index, item) {
                    let isRequiredAttribute = item.isRequired ? 'required' : '';
                    let displayHtml = `
                        <input type="text" class="form-control" name="payment_by" readonly
                        id="payment_by" placeholder="${item.placeholder}"  ${isRequiredAttribute}>
                    `;
                    $('#customer-info-display-div').append(displayHtml);
                });

            } else {
                $("#sectionViewModal #offline_payment_top_part").removeClass("active");
                $("#sectionViewModal #offline_payment_bottom_part").addClass("active");

                let methodName = $('#method_name').val();

                if (methodName !== '') {
                    $('#payment_modal_method_name').text(methodName + ' ' + 'Info');
                }

                function extractPaymentData() {
                    let data = [];

                    $('.field-row-payment').each(function (index) {
                        console.log('modal')
                        let title = $(this).find('input[name="title[]"]').val();
                        let dataValue = $(this).find('input[name="data[]"]').val();
                        data.push({title: title, data: dataValue});
                    });

                    return data;
                }

                let extractedData = extractPaymentData();


                function displayPaymentData() {
                    let displayDiv = $('#displayDataDiv');
                    let methodNameDisplay = $('#methodNameDisplay');
                    methodNameDisplay.empty();
                    displayDiv.empty();

                    let paymentElement = $('<span>').text('Payment Method');
                    let payementDataElement = $('<span>').html(methodName);

                    let dataRow = $('<div>').addClass('d-flex gap-3 align-items-center mb-2');
                    dataRow.append(paymentElement).append($('<span>').text(':')).append(payementDataElement);


                    methodNameDisplay.append(dataRow);

                    extractedData.forEach(function (item) {
                        let titleElement = $('<span>').text(item.title);
                        let dataElement = $('<span>').html(item.data);

                        let dataRow = $('<div>').addClass('d-flex gap-3 align-items-center');

                        if (item.title !== '') {
                            dataRow.append(titleElement).append($('<span>').text(':')).append(dataElement);
                            displayDiv.append(dataRow);
                        }

                    });
                }

                displayPaymentData();

                //customer info
                function extractCustomerData() {
                    let data = [];

                    $('.field-row-customer').each(function (index) {
                        let fieldName = $(this).find('input[name="field_name[' + index + ']"]').val();
                        let placeholder = $(this).find('input[name="placeholder[' + index + ']"]').val();
                        let isRequired = $(this).find('input[name="is_required[' + index + ']"]').prop('checked');
                        data.push({fieldName: fieldName, placeholder: placeholder, isRequired: isRequired});
                    });

                    return data;
                }

                let extractedCustomerData = extractCustomerData();
                $('#customer-info-display-div').empty();

                $.each(extractedCustomerData, function (index, item) {
                    let isRequiredAttribute = item.isRequired ? 'required' : '';
                    let displayHtml = `
                        <input type="text" class="form-control" name="payment_by" readonly
                            id="payment_by" placeholder="${item.placeholder}"  ${isRequiredAttribute}>
                    `;
                    $('#customer-info-display-div').append(displayHtml);
                });
            }

            $("#sectionViewModal").modal("show");
        }

        $(document).ready(function () {
            $("#bkashInfoModalButton").on('click', function () {
                console.log("something");
                let contentArgument = "bkashInfo";
                openModal(contentArgument);
            });
            $("#paymentInfoModalButton").on('click', function () {
                let contentArgument = "paymentInfo";
                openModal(contentArgument);
            });
        });
    </script>


    <script>
        function remove_field(fieldRowId) {
            $(`#field-row-customer--${fieldRowId}`).remove();
            counter--;
        }

        function remove_field_payment(fieldRowId) {
            $(`#field-row-payment--${fieldRowId}`).remove();
            counterPayment--;
        }

        jQuery(document).ready(function ($) {
            var counter = 0;
            var counterPayment = 0;

            $(document).on('click', '.remove-field-btn', function () {
                var counter = $(this).data('counter');
                remove_field(counter);
            });

            $(document).on('click', '.remove-field-payment-btn', function () {
                var counter = $(this).data('counter-payment');
                remove_field_payment(counter);
            });

            $('#add-more-field-customer').on('click', function (event) {
                if (counter < 14) {
                    event.preventDefault();

                    $('#custom-field-section-customer').append(
                        `<div id="field-row-customer--${counter}" class="field-row-customer">
                            <div class="row gy-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="field_name[${counter}]"
                                               placeholder="Select Field Name" value="" required>
                                        <label>{{translate('input_field_name')}} *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="placeholder[${counter}]"
                                               placeholder="Select placeholder" value="" required>
                                        <label>{{translate('placeholder')}} *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between gap-2 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="is_required[${counter}]" id="flexCheckDefault__${counter}" checked>
                                            <label class="form-check-label" for="flexCheckDefault__${counter}">
                                                {{translate('This_field_required')}}
                        </label>
                    </div>

                    <span class="text-danger cursor-pointer remove-field-btn" data-counter="${counter}">
                                            <span class="material-icons">delete</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>`
                    );

                    $(".js-select").select2();

                    counter++;
                } else {
                    Swal.fire({
                        title: '{{translate('Reached maximum')}}',
                        confirmButtonText: '{{translate('ok')}}',
                    });
                }
            })

            $('#add-more-field-payment').on('click', function (event) {
                if (counterPayment < 14) {
                    event.preventDefault();

                    $('#custom-field-section-payment').append(
                        `<div id="field-row-payment--${counterPayment}" class="field-row-payment">
                            <div class="row gy-3">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="title[]"
                                               placeholder="Select field name" value="" required>
                                        <label>{{translate('title')}} *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="data[]"
                                               placeholder="Select data" value="" required>
                                        <label>{{translate('data')}} *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-end">
                                        <span class="text-danger cursor-pointer remove-field-payment-btn" data-counter-payment="${counterPayment}">
                                            <span class="material-icons">delete</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>`
                    );

                    $(".js-select").select2();

                    counterPayment++;
                } else {
                    Swal.fire({
                        title: '{{translate('Reached maximum')}}',
                        confirmButtonText: '{{translate('ok')}}',
                    });
                }
            })

            $('form').on('reset', function (event) {
                if (counter > 1) {
                    $('#custom-field-section-payment').html("");
                    $('#custom-field-section-customer').html("");
                    $('#method_name').val("");
                    $('#payment_note').val("");
                }

                counter = 1;
            })
        });
    </script>

@endpush
