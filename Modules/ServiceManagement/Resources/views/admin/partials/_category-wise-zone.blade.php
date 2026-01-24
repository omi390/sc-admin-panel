<tr>
    <th scope="col">{{translate('variations')}}</th>
    <th scope="col">{{translate('default_price')}}</th>
    @foreach($zones as $zone)
        <th scope="col" data-zone-id="{{$zone->id}}">{{$zone->name}}</th>
    @endforeach
    <th scope="col">{{translate('image')}}</th>
    <th scope="col">{{translate('action')}}</th>
</tr>
