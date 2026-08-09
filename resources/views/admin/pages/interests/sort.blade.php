<div wire:init="loadScripts">
    <ul id="animated-thumbnails-gallery-interests" dir="ltr" class="list-group list-unstyled p-0 mb-3">
        @foreach($interests as $index => $interest)
            <li class="list-group-item order-data align-items-center" data-id="{{$interest['id']}}">
                <i class="icon-move mr-2 handle"></i>
                {{$interest['name']}}
            </li>
        @endforeach
    </ul>

    <button class="btn btn-secondary" style="display: none" id="save_order" wire:click="setOrder(localStorage.getItem('interestsOrder').split('|'));">
        {{__('pages/interests/sort.content.save')}}
    </button>
</div>
<script type="text/javascript">
    //add event listener to refresh file input
    window.addEventListener('loadScripts', () => {

        localStorage.removeItem('interestsOrder');

        let sortable = new Sortable(document.getElementById('animated-thumbnails-gallery-interests'), {
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
                    localStorage.setItem('interestsOrder', {!! json_encode($order) !!}.join('|'));
                    return {!! json_encode($order) !!};
                },


                /**
                 * Save the order of elements. Called onEnd (when the item is dropped).
                 * @param {Sortable}  sortable
                 */
                set: function (sortable) {
                    $('#save_order').show();
                    let order = sortable.toArray();
                    localStorage.setItem('interestsOrder', order.join('|'));
                }
            }
        });
    });
</script>
