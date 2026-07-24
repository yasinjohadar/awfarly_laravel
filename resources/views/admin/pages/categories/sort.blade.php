<div wire:init="loadScripts">
    <ul id="animated-thumbnails-gallery" dir="ltr" class="list-group list-unstyled p-0 mb-3">
        @foreach($categories as $index => $category)
            <li class="list-group-item order-data align-items-center" data-id="{{$category['id']}}">
                <i class="icon-move mr-2 handle"></i>
                {{$category['name']}}
            </li>
        @endforeach
    </ul>

    <button class="btn btn-secondary" style="display: none" id="save_order" wire:click="setOrder(localStorage.getItem('categoriesOrder').split('|'));">
        {{__('pages/categories/sort.content.save')}}
    </button>
</div>
<script type="text/javascript">
    //add event listener to refresh file input
    window.addEventListener('loadScripts', () => {

        localStorage.removeItem('categoriesOrder');

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
                    localStorage.setItem('categoriesOrder', {!! json_encode($order) !!}.join('|'));
                    return {!! json_encode($order) !!};
                },


                /**
                 * Save the order of elements. Called onEnd (when the item is dropped).
                 * @param {Sortable}  sortable
                 */
                set: function (sortable) {
                    $('#save_order').show();
                    let order = sortable.toArray();
                    localStorage.setItem('categoriesOrder', order.join('|'));
                }
            }
        });
    });
</script>


