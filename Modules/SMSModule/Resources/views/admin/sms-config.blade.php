@extends('adminmodule::layouts.master')

@section('title',translate('SMS_configuration'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('SMS_Gateway_Setup')}}</h2>
                    </div>

                    @if($publishedStatus == 1)
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <h4 class="card_heading">
                                    <i class="tio-info-outined"></i>
                                    {{ translate('Your current sms settings are disabled, because you have enabled
                                    sms gateway addon, To visit your currently active sms gateway settings please follow
                                    the link.') }}
                                    </h4>

                                <a href="{{!empty($paymentUrl) ? $paymentUrl : ''}}" class="btn btn-outline-primary">{{translate('settings')}}</a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        @php($is_published = $publishedStatus == 1 ? 'disabled' : '')
                        @foreach($dataValues as $key_value => $gateway)
                            <div class="col-12 col-md-6 mb-30">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="page-title">{{translate($gateway->key_name)}}</h4>
                                    </div>
                                    <div class="card-body p-30">
                                        <form action="{{route('admin.configuration.sms-set')}}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="discount-type">
                                                <div class="d-flex align-items-center gap-4 gap-xl-5 mb-30">
                                                    <div class="custom-radio">
                                                        <input type="radio" id="{{$gateway->key_name}}-active"
                                                               name="status"
                                                               value="1" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'checked':''}} {{$is_published}}>
                                                        <label for="{{$gateway->key_name}}-active">{{translate('active')}}</label>
                                                    </div>
                                                    <div class="custom-radio">
                                                        <input type="radio" id="{{$gateway->key_name}}-inactive"
                                                               name="status"
                                                               value="0" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'':'checked'}} {{$is_published}}>
                                                        <label for="{{$gateway->key_name}}-inactive">{{translate('inactive')}}</label>
                                                    </div>
                                                </div>

                                                <input name="gateway" value="{{$gateway->key_name}}" class="hide-div">
                                                <input name="mode" value="live" class="hide-div">

                                                @php($skip=['gateway','mode','status'])
                                                @foreach($dataValues->where('key_name',$gateway->key_name)->first()->live_values as $key=>$value)
                                                    @if(!in_array($key,$skip))
                                                        <div class="form-floating mb-30 mt-30">
                                                            <input type="text" class="form-control"
                                                                   name="{{$key}}"
                                                                   placeholder="{{translate($key)}} *"
                                                                   value="{{env('APP_ENV')=='demo'?'':$value}}" {{$is_published}}>
                                                            <label>{{translate($key)}} *</label>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn--primary demo_check" {{$is_published}}>
                                                    {{translate('update')}}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
