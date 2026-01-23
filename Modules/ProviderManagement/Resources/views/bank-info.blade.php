@extends('providermanagement::layouts.master')

@section('title',translate('bank_Info'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Provider_Bank_Information')}}</h2>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex gap-2 align-items-center mb-4">
                                <img width="20" src="{{asset('/public/assets/admin-module/img/icons/card.png')}}"
                                     alt="">
                                <h5 class="mb-0">{{translate('Account_Details')}}</h5>
                                <span class="material-symbols-outlined" data-bs-toggle="tooltip"
                                      data-bs-placement="bottom" title="{{translate('Please update your account details with accurate information. This information will be used by the admin for processing withdrawal request transaction
')}}">info</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-xl-5">
                                    <div class="provider-bank-card d-flex justify-content-between gap-3 p-4 border align-items-start flex-wrap">
                                        <div class="">
                                            <div class="d-flex gap-2 align-items-center mb-4">
                                                <span class="material-icons">person</span>
                                                {{translate('Holder Name')}}:
                                                <strong>{{$provider->bank_detail->acc_holder_name??''}}</strong>
                                            </div>

                                            <div class="d-flex flex-column gap-2">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('Bank Name')}}</span>:
                                                    <span>{{$provider->bank_detail->bank_name??''}}</span>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('branch_Name')}}</span>:
                                                    <span>{{$provider->bank_detail->branch_name??''}}</span>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('account_Name')}}</span>:
                                                    <span>{{$provider->bank_detail->acc_holder_name??''}}</span>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="min-w-100px">{{translate('routing_number')}}</span>:
                                                    <span>{{$provider->bank_detail->routing_number??''}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary d-flex gap-2"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                data-bs-whatever="@mdo">{{translate('edit')}}
                                            <span class="material-symbols-outlined m-0">edit</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">{{translate('general_information')}}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('provider.update_bank_info')}}" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-floating form-floating__icon mb-30">
                                    <input type="text" class="form-control" name="bank_name"
                                           placeholder="{{translate('Bank_Name')}}"
                                           value="{{$provider->bank_detail->bank_name??''}}" required>
                                    <label>{{translate('Bank_Name')}}</label>
                                    <span class="material-icons">account_balance</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating form-floating__icon mb-30">
                                    <input type="text" class="form-control" name="branch_name"
                                           placeholder="{{translate('Branch_Name')}}"
                                           value="{{$provider->bank_detail->branch_name??''}}" required>
                                    <label>{{translate('Branch_Name')}}</label>
                                    <span class="material-icons">store</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating form-floating__icon mb-30">
                                    <input type="text" class="form-control" name="acc_no"
                                           placeholder="{{translate('Account_No')}}"
                                           value="{{$provider->bank_detail->acc_no??''}}" required>
                                    <label>{{translate('Account_No')}}</label>
                                    <span class="material-icons">pin</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating form-floating__icon mb-30">
                                    <input type="text" class="form-control" name="acc_holder_name"
                                           placeholder="{{translate('A/C_Holder_Name')}}"
                                           value="{{$provider->bank_detail->acc_holder_name??''}}" required>
                                    <label>{{translate('A/C_Holder_Name')}}</label>
                                    <span class="material-icons">account_circle</span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-floating form-floating__icon mb-30">
                                    <input type="text" class="form-control" name="routing_number"
                                           placeholder="{{translate('routing_number')}}"
                                           value="{{$provider->bank_detail->routing_number??''}}"
                                           required>
                                    <label>{{translate('routing_number')}}</label>
                                    <span class="material-icons">monitoring</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 flex-wrap justify-content-end">
                            <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
