<div wire:init="loadScripts">
    <ul id="governorates_sort" dir="ltr" class="list-group list-unstyled p-0 mb-3">
        @foreach($governorates as $index => $governorate)
            <li class="list-group-item order-data align-items-center" data-id="{{$governorate['id']}}">
                <i class="icon-move handle mr-2"></i>
                {{$governorate['name']}}
            </li>
        @endforeach
    </ul>

    <button class="btn btn-secondary" style="display: none" id="save_order"
            wire:click="setOrder(localStorage.getItem('governoratesOrder').split('|'));">
        {{__('pages/countries/inquiry.content.save')}}
    </button>
</div>
<script type="text/javascript">
    window.addEventListener('loadScripts', () => {
        Sortable.create(document.getElementById('governorates_sort'), {
            swapThreshold: 1,
            animation: 150,
            handle: '.handle',
            group: "governoratesOrder",
            store: {
                get: function () {
                    localStorage.setItem('governoratesOrder', {!! json_encode($order) !!}.join('|'));
                    return {!! json_encode($order) !!};
                },
                set: function (sortable) {
                    $('#save_order').show();
                    let order = sortable.toArray();
                    localStorage.setItem('governoratesOrder', order.join('|'));
                }
            }
        });
    });
</script>
