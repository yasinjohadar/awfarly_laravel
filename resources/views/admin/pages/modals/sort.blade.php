<div wire:init="loadScripts">
    <ul id="animated-thumbnails-gallery" dir="ltr" class="list-group list-unstyled p-0 mb-3">
        @foreach($modals as $index => $modal)
            <li class="list-group-item order-data align-items-center" data-id="{{$modal['id']}}">
                <i class="icon-move mr-2 handle"></i>
                {{$modal['name']}}
            </li>
        @endforeach
    </ul>

    <button class="btn btn-secondary" style="display: none" id="save_order" wire:click="setOrder(localStorage.getItem('modalsOrder').split('|'));">
        {{__('pages/modals/sort.content.save')}}
    </button>
</div>
<script type="text/javascript">
    //add event listener to refresh file input
    window.addEventListener('loadScripts', () => {

        localStorage.removeItem('modalsOrder');

        let sortable = new Sortable(document.getElementById('animated-thumbnails-gallery'), {
            swapThreshold: 1,
            animation: 150,
            handle: '.handle', // handle's class
            store: {
                /**
                 * Get the order of elements. Called once during initialization.
                 * @param   {Sortable}  sortable
                 * @returns {Array}
                 */
                get: function (sortable) {
                    localStorage.setItem('modalsOrder', {!! json_encode($order) !!}.join('|'));
                    return {!! json_encode($order) !!};
                },


                /**
                 * Save the order of elements. Called onEnd (when the item is dropped).
                 * @param {Sortable}  sortable
                 */
                set: function (sortable) {
                    $('#save_order').show();
                    let order = sortable.toArray();
                    localStorage.setItem('modalsOrder', order.join('|'));
                }
            }
        });
    });
</script>


