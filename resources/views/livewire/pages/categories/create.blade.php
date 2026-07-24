<div class="card-body">
    <form wire:submit.prevent="store">
        {{--<div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="parent">{{__('pages/categories/create.content.inputs.parent')}}</label>
            <div class="col-lg-10">
                <select class="form-control select2" id="parent" wire:model.defer="parent_category_id">
                    <option></option>
                    @foreach ($categories as $item)
                        <option value="{{$item['id']}}">{{$item['name']}}</option>
                    @endforeach
                </select>
                <div class="small text-secondary">
                    {{__('pages/categories/create.content.inputs.parent_note')}}
                </div>
                @error('parent_category_id')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>--}}
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_en">{{__('pages/categories/create.content.inputs.name_en')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en"
                       name="name_en" wire:model.defer="name_en"
                       />
                @error('name_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_ar">{{__('pages/categories/create.content.inputs.name_ar')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar"
                       name="name_ar" wire:model.defer="name_ar"
                       />
                @error('name_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        {{--<div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="description">{{__('pages/categories/create.content.inputs.description')}}</label>
            <div class="col-lg-10">
                <textarea type="text" class="form-control @error('description') is-invalid @enderror"
                          id="description"
                          wire:model.defer="description"
                          ></textarea>
                @error('description')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>--}}
        <div class="form-group row">
            <label for="image"
                   class="col-form-label col-lg-2">{{__('pages/categories/create.content.inputs.placeholders.choose_file')}}</label>
            <div class="col-lg-10">
                <input type="file" wire:model.defer="image" class="form-control h-auto" id="image">
                @error('image') <span class="error">{{ $message }}</span> @enderror
            </div>
            @if($image)
                <img alt="{{$image}}" class="img-fluid mt-2" width="240" src="{{ $image->temporaryUrl() }}">
            @endif
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/categories/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>


@push('scripts')
    <script type="text/javascript">
        /*document.addEventListener('livewire:load', function (){
            $('#parent').select2({
                placeholder: '{{__('pages/categories/inquiry.modal.edit.inputs.placeholders.categories')}}',
                allowClear: true,

            });
        })*/
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#image').val(null);
        });

       /* //add event listener to refresh select2 function
        window.addEventListener('refreshSelect2Create', () => {
            $('#parent').select2({
                placeholder: '{{__('pages/categories/inquiry.modal.edit.inputs.placeholders.categories')}}',
                allowClear: true,

            });
        });
        //start function on change.
        $('#parent').on('change', function (e) {
            let item = $('#parent').select2("val");
            window.livewire.emit('setParentCategoryCreate', item);
        });*/
    </script>
@endpush
