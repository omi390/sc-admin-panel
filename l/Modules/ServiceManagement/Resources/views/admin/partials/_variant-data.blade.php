
@if(session()->has('variations'))
    @foreach(session('variations') as $key=>$item)
        <tr>
            <th scope="row">
                {{$item['variant']}}
                <input name="variants[]" value="{{str_replace(' ','-',$item['variant'])}}" class="hide-div">
            </th>
            <td>
                <input type="number" value="{{$item['price']}}" class="theme-input-style" id="default-set-{{$key}}"
                       onkeyup="set_values('{{$key}}')" step="any">
            </td>
            @foreach($zones as $zone)
                <td>
                    <input type="number" name="{{$item['variant_key']}}_{{$zone->id}}_price" value="{{$item['price']}}"
                           class="theme-input-style default-get-{{$key}}" step="any">
                </td>
            @endforeach
            <td>
                <a class="btn btn--danger service-ajax-remove-variant"
                   data-id="variation-table"
                   data-route="{{route('admin.service.ajax-remove-variant',[$item['variant_key']])}}">
                    <span class="material-icons m-0">delete</span>
                </a>
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
            ajax_remove_variant(route, id);
        });
    });

    function set_values(key) {
        document.querySelectorAll('.default-get-' + key).forEach(function(element) {
            element.value = document.getElementById('default-set-' + key).value;
        });
    }

</script>
