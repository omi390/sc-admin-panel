<div class="modal-header border-0 pb-0 d-flex justify-content-end">
    <button
        type="button"
        class="btn-close border-0"
        data-bs-dismiss="modal"
        aria-label="Close"
    ><i class="tio-clear"></i></button>
</div>
<div class="modal-body px-4 px-sm-5">
    <div class="mb-4 text-center">
        @php($logo=\Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','business_logo')->first()->value)
        <img width="200"
             src="{{onErrorImage(
                $logo,
                asset('storage/app/public/restaurant').'/' . $logo,
                asset('public/assets/admin-module/img/img1.jpg') ,
                'restaurant/')}}"
             alt="{{ translate('logo') }}"
             class="dark-support onerror-image"
        />
    </div>
    <h2 class="text-center mb-4">{{$addonName}}</h2>

    <form action="{{route('admin.addon.activation')}}" method="post" id="customer_login_modal" autocomplete="off">
        @csrf
        <div class="form-group mb-4">
            <label for="username">{{ translate('Codecanyon') }} {{ translate('username') }}</label>
            <input
                    name="username" id="username"
                    class="form-control"
                    placeholder="{{translate('Ex:_John_Roy')}}" required
            />
        </div>
        <div class="form-group mb-6">
            <label for="purchase_code">{{ translate('Purchase') }} {{ translate('Code') }}</label>
            <input
                    name="purchase_code" id="purchase_code"
                    class="form-control"
                    placeholder="{{translate('Ex: 19xxxxxx-ca5c-49c2-83f6-696a738b0000')}}" required
            />
            <input type="text" name="path" class="form-control" value="{{$path}}" hidden>
        </div>

        <div class="d-flex justify-content-center gap-3 mb-3">
            <button type="button" class="fs-16 btn btn-secondary flex-grow-1"
                    data-bs-dismiss="modal">{{ translate('cancel') }}</button>
            <button type="submit" class="fs-16 btn btn--primary flex-grow-1">{{ translate('Activate') }}</button>
        </div>
    </form>
</div>
