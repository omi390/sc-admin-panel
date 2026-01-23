<h4 class="mb-2">{{translate('Lead_Service_Information')}}</h4>
@if(isset($booking->serviceman))
    <h5 class="c1 mb-3">{{Str::limit($booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->first_name.' '.$booking->serviceman->user->last_name:'', 30)}}</h5>
    <ul class="list-info">
        <li>
            <span class="material-icons">phone_iphone</span>
            <a href="tel:{{$booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone:''}}">
                {{$booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone:''}}
            </a>
        </li>
    </ul>
@else
    <div class="d-flex flex-column gap-2 mt-30 align-items-center">
        <span class="material-icons text-muted fs-2">account_circle</span>
        <p class="text-muted text-center fw-medium">{{translate('No Serviceman Information')}}</p>
    </div>
@endif
