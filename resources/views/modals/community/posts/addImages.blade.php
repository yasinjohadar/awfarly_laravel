<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$post['id'] ?? null}})">
    <x-slot name="title">
        {{__('pages/community/posts/index.modal.edit.title')}}
    </x-slot>
    <x-slot name="content">
        <div class="form-group">
            <label for="content">{{__('pages/community/posts/inquiry.content.content')}}</label>
            <textarea rows="7" class="form-control @error('content') is-invalid @enderror" id="content"
                      wire:model.defer="content"></textarea>
            @error('content')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group" wire:ignore x-data="{category_id: @entangle('category_id').defer,}"
             x-init="$nextTick(() => {select2 = $($refs.select).select2().val(category_id).change();select2.on('change', (event) => {category_id = event.target.value;});})"
        >
            <label for="category_id">{{__('pages/community/posts/inquiry.content.category')}}</label>
            <select x-model="category_id" x-cloak
                    data-placeholder="{{__('pages/community/posts/inquiry.content.category')}}"
                    id="category_id"
                    class="form-control select2 @error('category_id') is-invalid @enderror"
                    x-ref="select"
                    x-bind:value="category_id">
                @foreach($categories as $category)
                    @isset($category['children'])
                        <optgroup label="{{$category['name']}}">
                            @foreach($category['children'] as $child)
                                <option value="{{$child['id']}}">{{$child['name']}}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                    @endisset
                @endforeach
            </select>
            @error('category_id')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        <div class="form-group" wire:ignore>
            <label for="media">{{__('pages/community/posts/inquiry.content.images')}}</label>
            <input type="file" class="form-control h-100 @error('media') is-invalid @enderror"
                   id="media"
                   name="media"
                   wire:model.defer="media" multiple>
            @error('media')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
            {{__('pages/community/posts/index.modal.edit.cancel')}}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" type="submit">
            {{__('pages/community/posts/index.modal.edit.submit')}}
        </x-primary-button>
    </x-slot>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
