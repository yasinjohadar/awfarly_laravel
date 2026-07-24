<div>
    <div class="card">
        <div class="card-header font-weight-bold">
            {{__('frontend/categories/pages.title')}}
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center" style="overflow-x: auto">
                @if(isset($category_id) && $category_id)
                    <div class="p-2">
                        <a class="cursor-pointer align-content-center" wire:click="setCategoryId(null)">
                            <i class=" {{app()->getLocale() === 'ar' ? 'ti-back-right' : 'ti-back-left'}} h3"></i>
                        </a>
                        <div>{{__('frontend/categories/pages.back')}}</div>
                    </div>
                @endif
                @foreach($categories as $category)
                    @if(isset($category['subCategories']) && count($category['subCategories']) > 0)
                        @foreach($category['subCategories'] as $subCategory)
                            <div class="p-2 text-center">
                                <a class="cursor-pointer" wire:click="setCategoryId({{$subCategory['id']}})">
                                    <img
                                        class="rounded-circle{{$subCategory['id'] == $category_id ? ' border border-primary' : ''}}"
                                        height="64" width="64" alt="{{$subCategory['name']}}"
                                        title="{{$subCategory['name']}}"
                                        src="{{$subCategory['image']}}"/>
                                </a>
                                <div
                                    class="text-center text-nowrap{{$subCategory['id'] == $category_id ? ' text-primary' : ''}}">{{$subCategory['name']}}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-2 text-center">
                            <a class="cursor-pointer" wire:click="setCategoryId({{$category['id']}})">
                                <img
                                    class="rounded-circle{{$category['id'] == $category_id ? ' border border-primary' : ''}}"
                                    height="64" width="64" alt="{{$category['name']}}"
                                    title="{{$category['name']}}"
                                    src="{{$category['image']}}"/>
                            </a>
                            <div
                                class="text-center text-nowrap{{$category['id'] == $category_id ? ' text-primary' : ''}}">{{$category['name']}}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
