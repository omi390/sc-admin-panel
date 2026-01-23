<div class="modal fade" id="serviceAddressModal--{{$booking['id']}}" tabindex="-1"
     aria-labelledby="serviceAddressModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{route('admin.booking.service_address_update', [$booking['service_address_id']])}}"
              method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body m-4">
                    <div class="d-flex flex-column gap-2 align-items-center">
                        <img width="75" class="mb-2"
                             src="{{asset('public/assets/provider-module')}}/img/media/address.jpg"
                             alt="">
                        <h3>{{translate('Update customer service address')}}</h3>

                        <div class="row mt-4">
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="city"
                                               placeholder="{{translate('city')}} *"
                                               value="{{$customerAddress?->city}}" required>
                                        <label>{{translate('city')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="street"
                                               placeholder="{{translate('street')}} *"
                                               value="{{$customerAddress?->street}}" required>
                                        <label>{{translate('street')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="zip_code"
                                               placeholder="{{translate('zip_code')}} *"
                                               value="{{$customerAddress?->zip_code}}" required>
                                        <label>{{translate('zip_code')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="country"
                                               placeholder="{{translate('country')}} *"
                                               value="{{$customerAddress?->country}}" required>
                                        <label>{{translate('country')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="address"
                                               placeholder="{{translate('address')}} *"
                                               value="{{$customerAddress?->address}}" required>
                                        <label>{{translate('address')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="contact_person_name"
                                               placeholder="{{translate('contact_person_name')}} *"
                                               value="{{$customerAddress?->contact_person_name}}" required>
                                        <label>{{translate('contact_person_name')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="contact_person_number"
                                               placeholder="{{translate('contact_person_number')}} *"
                                               value="{{$customerAddress?->contact_person_number}}" required>
                                        <label>{{translate('contact_person_number')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <select class="js-select theme-input-style w-100" name="address_label">
                                        <option selected disabled>{{translate('Select_address_label')}}*</option>
                                        <option
                                            value="home" {{$customerAddress?->address_label == 'home' ? 'selected' : ''}}>{{translate('Home')}}</option>
                                        <option
                                            value="office" {{$customerAddress?->address_label == 'office' ? 'selected' : ''}}>{{translate('Office')}}</option>
                                        <option
                                            value="others" {{$customerAddress?->address_label == 'others' ? 'selected' : ''}}>{{translate('others')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <select class="js-select select-zone theme-input-style w-100" name="zone_id">
                                        <option value="" disabled>{{translate('Select zone')}}</option>
                                        @foreach($zones as $zone)
                                            <option
                                                value="{{$zone?->id}}" {{$zone?->id == $customerAddress?->zone_id ? 'selected' : null}}>{{$zone?->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="latitude" id="latitude"
                                               placeholder="{{translate('lat')}} *"
                                               value="{{$customerAddress?->lat}}" required readonly
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="{{translate('Select from map')}}">
                                        <label>{{translate('lat')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="longitude" id="longitude"
                                               placeholder="{{translate('lon')}} *"
                                               value="{{$customerAddress?->lon}}" required readonly
                                               data-bs-toggle="tooltip" data-bs-placement="top"
                                               title="{{translate('Select from map')}}">
                                        <label>{{translate('lon')}} *</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" id="location_map_div" style="height: 250px">
                                <input id="pac-input" class="controls rounded" data-toggle="tooltip"
                                       data-placement="right"
                                       data-original-title="{{ translate('search_your_location_here') }}"
                                       type="text" placeholder="{{ translate('search_here') }}"/>
                                <div id="location_map_canvas" class="overflow-hidden rounded mt-4"
                                     style="height: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-3 border-0 pt-0 pb-4 m-4">
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal" aria-label="Close">
                        {{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn--primary">{{translate('Update')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>
