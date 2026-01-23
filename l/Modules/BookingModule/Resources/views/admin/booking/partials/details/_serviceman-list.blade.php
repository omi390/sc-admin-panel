<option value="no_serviceman">{{translate('Select Serviceman')}}</option>
@foreach($servicemen as $serviceman)
    <option
        value="{{$serviceman->id}}" {{$booking->serviceman_id == $serviceman->id ? 'selected' : ''}} >
        {{$serviceman->user ? Str::limit($serviceman->user->first_name.' '.$serviceman->user->last_name, 30): ''}}
    </option>
@endforeach

