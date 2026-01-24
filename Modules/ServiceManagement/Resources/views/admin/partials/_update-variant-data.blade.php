
@if(isset($variants))
    @php
        // Separate provider variants from regular variants
        $providerVariants = $variants->whereNotNull('provider_id');
        $regularVariants = $variants->whereNull('provider_id');
        $variant_keys = $regularVariants->pluck('variant_key')->unique()->toArray();
    @endphp
    
    {{-- Regular variants (for all zones) --}}
    @foreach($variant_keys as $key=>$item)
        <tr>
            <th scope="row">
                {{str_replace('-',' ',$item)}}
                <input name="variants[]" value="{{$item}}" class="hide-div">
            </th>
            <td>
                <input type="number"
                       value="{{$regularVariants->where('price','>',0)->where('variant_key',$item)->first()->price??0}}"
                       class="theme-input-style" id="default-set-{{$key}}-update"
                       onkeyup="set_update_values('{{$key}}')">
            </td>
            @foreach($zones as $zone)
                <td>
                    <input type="number" name="{{$item}}_{{$zone->id}}_price"
                           value="{{$regularVariants->where('zone_id',$zone->id)->where('variant_key',$item)->first()->price??0}}"
                           class="theme-input-style default-get-{{$key}}-update">
                </td>
            @endforeach
            <td>
                <span class="text-muted">-</span>
            </td>
            <td>
                <a class="btn btn-sm btn--danger service-ajax-remove-variant"
                   data-route="{{ route('admin.service.ajax-delete-db-variant',[$item,$variants->first()->service_id]) }}"
                   data-id="variation-update-table">
                    <span class="material-icons m-0">delete</span>
                </a>
            </td>
        </tr>
    @endforeach

    {{-- Provider-specific variants --}}
    @foreach($providerVariants as $key=>$variant)
        <tr>
            <th scope="row">
                <div>
                    <strong>{{$variant->variant}}</strong>
                    <br><small class="text-muted">
                        @if($variant->provider)
                            <strong>{{translate('provider')}}:</strong> {{$variant->provider->company_name ?? 'N/A'}}<br>
                        @endif
                        @if($variant->zone)
                            <strong>{{translate('zone')}}:</strong> {{$variant->zone->name ?? 'N/A'}}
                        @endif
                    </small>
                </div>
                <input type="hidden" name="provider_variants[{{$key}}][provider_id]" value="{{$variant->provider_id}}">
                <input type="hidden" name="provider_variants[{{$key}}][zone_id]" value="{{$variant->zone_id}}">
                <input type="hidden" name="provider_variants[{{$key}}][variant_key]" value="{{$variant->variant_key}}">
                <input type="hidden" name="provider_variants[{{$key}}][price]" value="{{$variant->price}}">
                @if($variant->variation_image)
                    <input type="hidden" name="provider_variants[{{$key}}][variation_image]" value="{{$variant->variation_image}}">
                @endif
            </th>
            <td>
                <input type="number" value="{{$variant->price}}" 
                       name="provider_variants[{{$key}}][price]" 
                       class="theme-input-style" step="any">
            </td>
            <td>
                @if($variant->variation_image)
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{$variant->variation_image}}" alt="Variant Image" 
                             style="max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                             onerror="this.src='{{asset('public/assets/admin-module/img/media/upload-file.png')}}'">
                        <a href="{{$variant->variation_image}}" target="_blank" class="btn btn-sm btn--primary" title="{{translate('view_image')}}">
                            <span class="material-icons" style="font-size: 16px;">open_in_new</span>
                        </a>
                    </div>
                @else
                    <span class="text-muted">{{translate('no_image')}}</span>
                @endif
            </td>
            <td colspan="{{count($zones) > 0 ? count($zones) - 1 : 0}}" class="text-center text-muted">
                {{translate('provider_specific_variant')}}
            </td>
            <td>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn--primary edit-provider-variant-btn"
                       data-variant-id="{{$variant->id}}"
                       data-variant-name="{{$variant->variant}}"
                       data-variant-price="{{$variant->price}}"
                       data-variant-image="{{$variant->variation_image ?? ''}}"
                       data-variant-key="{{$variant->variant_key}}"
                       data-service-id="{{$variant->service_id}}"
                       data-provider-id="{{$variant->provider_id}}"
                       data-zone-id="{{$variant->zone_id}}"
                       data-bs-toggle="modal"
                       data-bs-target="#editProviderVariantModal">
                        <span class="material-icons m-0" style="font-size: 18px;">edit</span>
                    </a>
                    <a class="btn btn-sm btn--danger service-ajax-remove-variant"
                       data-route="{{ route('admin.service.ajax-delete-db-variant',[$variant->variant_key,$variant->service_id]) }}"
                       data-id="variation-update-table"
                       data-provider-id="{{$variant->provider_id}}"
                       data-zone-id="{{$variant->zone_id}}">
                        <span class="material-icons m-0">delete</span>
                    </a>
                </div>
            </td>
        </tr>
    @endforeach
@endif

<script>
    "use strict";
    document.addEventListener('DOMContentLoaded', function () {
        var elements = document.querySelectorAll('.service-ajax-remove-variant');
        elements.forEach(function (element) {
            element.addEventListener('click', function () {
                var route = this.getAttribute('data-route');
                var id = this.getAttribute('data-id');
                ajax_remove_variant(route, id);
            });
        });

        function set_update_values(key) {
            var updateElements = document.querySelectorAll('.default-get-' + key + '-update');
            var setValue = document.getElementById('default-set-' + key + '-update').value;
            updateElements.forEach(function (element) {
                element.value = setValue;
            });
        }
    });
</script>
