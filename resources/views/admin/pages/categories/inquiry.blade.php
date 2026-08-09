<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        @if($category)
            <h5 class="card-title mb-0">{!! __('pages/categories/index.content.title', ['name' => $category->{(App::getLocale() === 'ar' ? 'name_ar' : 'name_en')}]) !!}</h5>
        @else
            <h5 class="card-title mb-0">{{__('pages/categories/inquiry.content.title')}}</h5>
            @can('categories.add')
                <a href="{{route('admin.categories.create')}}" class="btn btn-primary">
                    {{__('pages/categories/inquiry.content.add')}}
                </a>
            @endcan
        @endif
    </div>

    <div class="card-body">
        <div class="form-group">
            @if($category)
                <button class="btn btn-secondary"
                        @if(isset($order) && $order == true) wire:click="$emitSelf('setCategoryId', {{$category->id}}, false)"
                        @else
                        wire:click="$emitSelf('setCategoryId', null)" @endisset>{{__('pages/categories/index.content.back')}}</button>
                @empty($order)
                    @can('categories.add')
                        <button class="btn btn-primary"
                                wire:click="$emitTo('categories.category-inquiry-component', 'showAddModal')">{{__('pages/categories/index.content.add')}}</button>
                    @endcan
                    @if($category->childCategories()->count() > 0)
                        <button class="btn btn-secondary"
                                wire:click="$emitSelf('setCategoryId', {{$category->id}}, true)">{{__('pages/categories/index.content.sort')}}</button>
                    @endif
                @endempty
            @else
                @if($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setCategoryId', null)">{{__('pages/categories/index.content.back')}}</button>
                @endif
                @empty($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setCategoryId', null, true)">{{__('pages/categories/index.content.sort')}}</button>
                @endempty
            @endif
        </div>
        @if($category)
            @if($order)
                @livewire('categories.category-sort-component', ['category_id' => $category->id])
            @else
                @livewire('categories.category-inquiry-component', ['category_id' => $category->id])
            @endif
        @else
            @if($order)
                @livewire('categories.category-sort-component')
            @else
                @livewire('categories.categories-inquiry-component')
            @endempty
        @endif
    </div>
</div>


@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearAddFileInput', () => {
            $('#category_image').val(null);
        });

        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#new_image').val(null);
        });
    </script>
@endpush
