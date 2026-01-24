<div class="d-flex justify-content-between gap-2">
    <h4 class="mb-2">{{translate('Provider Information')}}</h4>
    @if($booking->provider)
        <span class="square-btn" data-bs-toggle="modal" data-bs-target="#providerModal">
            <i class="material-icons fs-14" data-toggle="tooltip" data-placement="top"
               title="{{translate('Update service address')}}">edit</i>
        </span>
    @endif
</div>
@if(isset($booking->provider))
    <h5 class="c1 mb-3">{{Str::limit($booking->provider->company_name??'', 30)}}</h5>
    <ul class="list-info">
        <li>
            <span class="material-icons">phone_iphone</span>
            <a href="tel:{{$booking->provider->contact_person_phone??''}}">{{$booking->provider->contact_person_phone??''}}</a>
        </li>
        <li>
            <span class="material-icons">map</span>
            <p>{{Str::limit($booking->provider->company_address??'', 100)}}</p>
        </li>
    </ul>
@else
    <p class="text-muted text-center mt-30 fz-12">{{translate('No Provider Information')}}</p>
@endif
