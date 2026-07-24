<div wire:init="loadScripts">
    <ul id="countries_sort" dir="ltr" class="list-group list-unstyled p-0 mb-3">
        @foreach($countries as $index => $country)
            <li class="list-group-item order-data align-items-center" data-id="{{$country['id']}}">
                <i class="icon-move handle mr-2"></i>
                {{$country['name']}}
            </li>
        @endforeach
    </ul>

    <button style="display: none" id="save_order" class="btn btn-secondary"
            wire:click="setOrder(localStorage.getItem('countriesOrder').split('|'));">
        {{__('pages/countries/inquiry.content.save')}}
    </button>
</div>
<script type="text/javascript">
    //add event listener to refresh file input
    window.addEventListener('loadScripts', () => {
        let sortable = Sortable.create(document.getElementById('countries_sort'), {
            swapThreshold: 1,
            animation: 150,
            handle: '.handle',
            group: "countriesOrder",
            store: {
                /**
                 * Get the order of elements. Called once during initialization.
                 * @param   {Sortable}  sortable
                 * @returns {Array}
                 */
                get: function (sortable) {
                    localStorage.setItem('countriesOrder', {!! json_encode($order) !!}.join('|'));
                    return {!! json_encode($order) !!};
                },


                /**
                 * Save the order of elements. Called onEnd (when the item is dropped).
                 * @param {Sortable}  sortable
                 */
                set: function (sortable) {
                    $('#save_order').show();
                    let order = sortable.toArray();
                    localStorage.setItem('countriesOrder', order.join('|'));
                }
            }
        });
    });
</script>
