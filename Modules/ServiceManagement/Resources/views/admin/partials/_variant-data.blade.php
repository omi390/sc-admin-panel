
@if(session()->has('variations'))
    @foreach(session('variations') as $key=>$item)
        @php
            $isProviderVariant = isset($item['provider_id']) && isset($item['zone_id']);
        @endphp
        <tr>
            <th scope="row">
                <div>
                    {{$item['variant']}}
                    @if($isProviderVariant)
                        <br><small class="text-muted">
                            @php
                                $provider = \Modules\ProviderManagement\Entities\Provider::find($item['provider_id']);
                                $zone = \Modules\ZoneManagement\Entities\Zone::find($item['zone_id']);
                            @endphp
                            @if($provider)
                                <strong>{{translate('provider')}}:</strong> {{$provider->company_name ?? 'N/A'}}<br>
                            @endif
                            @if($zone)
                                <strong>{{translate('zone')}}:</strong> {{$zone->name ?? 'N/A'}}
                            @endif
                        </small>
                    @endif
                    @if(isset($item['variation_image']) && $item['variation_image'])
                        <br><img src="{{$item['variation_image']}}" alt="Variant Image" style="max-width: 50px; max-height: 50px; margin-top: 5px;">
                    @endif
                </div>
                <input name="variants[]" value="{{str_replace(' ','-',$item['variant'])}}" class="hide-div">
                @if($isProviderVariant)
                    <input type="hidden" name="provider_variants[{{$key}}][provider_id]" value="{{$item['provider_id']}}">
                    <input type="hidden" name="provider_variants[{{$key}}][zone_id]" value="{{$item['zone_id']}}">
                    <input type="hidden" name="provider_variants[{{$key}}][variant_key]" value="{{$item['variant_key']}}">
                    <input type="hidden" name="provider_variants[{{$key}}][price]" value="{{$item['price']}}">
                    @if(isset($item['variation_image']))
                        <input type="hidden" name="provider_variants[{{$key}}][variation_image]" value="{{$item['variation_image']}}">
                    @endif
                @endif
            </th>
            <td>
                @if($isProviderVariant)
                    <input type="number" value="{{$item['price']}}" class="theme-input-style" 
                           name="provider_variants[{{$key}}][price]" step="any" readonly>
                @else
                    <input type="number" value="{{$item['price']}}" class="theme-input-style" id="default-set-{{$key}}"
                           onkeyup="set_values('{{$key}}')" step="any">
                @endif
            </td>
            @if(!$isProviderVariant)
                @foreach($zones as $zone)
                    <td>
                        <input type="number" name="{{$item['variant_key']}}_{{$zone->id}}_price" value="{{$item['price']}}"
                               class="theme-input-style default-get-{{$key}}" step="any">
                    </td>
                @endforeach
            @else
                <td colspan="{{count($zones)}}" class="text-center text-muted">
                    {{translate('provider_specific_variant')}}
                </td>
            @endif
            <td>
                @if(isset($item['variation_image']) && $item['variation_image'])
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{$item['variation_image']}}" alt="Variant Image" 
                             style="max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                             onerror="this.src='{{asset('public/assets/admin-module/img/media/upload-file.png')}}'">
                        <a href="{{$item['variation_image']}}" target="_blank" class="btn btn-sm btn--primary" title="{{translate('view_image')}}">
                            <span class="material-icons" style="font-size: 16px;">open_in_new</span>
                        </a>
                    </div>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($isProviderVariant)
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn--primary edit-provider-variant-btn"
                           data-variant-name="{{$item['variant']}}"
                           data-variant-price="{{$item['price']}}"
                           data-variant-image="{{$item['variation_image'] ?? ''}}"
                           data-variant-key="{{$item['variant_key']}}"
                           data-provider-id="{{$item['provider_id']}}"
                           data-zone-id="{{$item['zone_id']}}"
                           data-bs-toggle="modal"
                           data-bs-target="#editProviderVariantModal">
                            <span class="material-icons m-0" style="font-size: 18px;">edit</span>
                        </a>
                        <a class="btn btn--danger service-ajax-remove-variant"
                           data-id="variation-table"
                           data-route="{{route('admin.service.ajax-remove-variant',[$item['variant_key']])}}"
                           data-provider-id="{{$item['provider_id']}}"
                           data-zone-id="{{$item['zone_id']}}">
                            <span class="material-icons m-0">delete</span>
                        </a>
                    </div>
                @else
                    <a class="btn btn--danger service-ajax-remove-variant"
                       data-id="variation-table"
                       data-route="{{route('admin.service.ajax-remove-variant',[$item['variant_key']])}}">
                        <span class="material-icons m-0">delete</span>
                    </a>
                @endif
            </td>
        </tr>
    @endforeach
@endif

<script>
    "use strict";

    // Equivalent JavaScript code
    document.querySelectorAll('.service-ajax-remove-variant').forEach(function(element) {
        element.addEventListener('click', function() {
            var route = this.getAttribute('data-route');
            var id = this.getAttribute('data-id');
            var providerId = this.getAttribute('data-provider-id');
            var zoneId = this.getAttribute('data-zone-id');
            if (typeof ajax_remove_variant === 'function') {
                ajax_remove_variant(route, id, providerId, zoneId);
            }
        });
    });

    function set_values(key) {
        document.querySelectorAll('.default-get-' + key).forEach(function(element) {
            element.value = document.getElementById('default-set-' + key).value;
        });
    }

</script>
