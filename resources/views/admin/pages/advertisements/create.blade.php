@extends('admin.layouts.app')

@section('title', __('pages/advertisements/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/advertisements/create.breadcrumb.advertisements')}}</div>
    <div class="breadcrumb-item active">{{__('pages/advertisements/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisements/create.content.title')}}</h5>
        </div>
        <div class="card-body">
            @include('globals.messages')
            <form action="{{route('admin.advertisements.store')}}" method="post" enctype="multipart/form-data"
                  autocomplete="off">
                @csrf
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="advertiser_name">{{__('pages/advertisements/create.content.inputs.name')}}</label>
                    <div class="col-lg-11">
                        <input type="text" class="form-control @error('advertiser_name') is-invalid @enderror"
                               id="advertiser_name"
                               name="advertiser_name" value="{{old('advertiser_name')}}">
                        @error('advertiser_name')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="advertiser_url">{{__('pages/advertisements/create.content.inputs.url')}}</label>
                    <div class="col-lg-11">
                        <input type="text" class="form-control @error('advertiser_url') is-invalid @enderror"
                               id="advertiser_url"
                               name="advertiser_url" value="{{old('advertiser_url')}}">
                        @error('advertiser_url')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="advertiser_image">{{__('pages/advertisements/create.content.inputs.advertiser_image')}}</label>
                    <div class="col-lg-11">
                        <input type="file" class="form-control h-auto @error('advertiser_image') is-invalid @enderror"
                               id="advertiser_image"
                               name="advertiser_image">
                        @error('advertiser_image')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <hr/>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1" for="content">
                        {{__('pages/advertisements/create.content.inputs.content')}}
                    </label>
                    <div class="col-lg-11">
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content"
                                  name="content">{{old('content')}}</textarea>
                        @error('content')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="media">{{__('pages/advertisements/create.content.inputs.files')}}</label>
                    <div class="col-lg-11">
                        <input type="file" multiple class="form-control h-auto @error('media') is-invalid @enderror"
                               id="media"
                               name="media[]">
                        @error('media')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <hr/>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="type">{{__('pages/advertisements/create.content.inputs.type')}}</label>
                    <div class="col-lg-11">
                        <select class="form-control @error('type') is-invalid @enderror" name="type" id="type" required>
                            <option value="any" @if(old('type') === 'any') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.type_values.any')}}
                            </option>
                            <option value="website" @if(old('type') === 'website') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.type_values.website')}}
                            </option>
                            <option value="mobile" @if(old('type') === 'mobile') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.type_values.mobile')}}
                            </option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="users">{{__('pages/advertisements/create.content.inputs.users')}}</label>
                    <div class="col-lg-11">
                        <select class="form-control @error('users') is-invalid @enderror" name="users" id="users"
                                required>
                            <option value="any" @if(old('users') === 'any') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.users_values.any')}}
                            </option>
                            <option value="advertisers" @if(old('users') === 'advertisers') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.users_values.advertisers')}}
                            </option>
                            <option value="customers" @if(old('users') === 'customers') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.users_values.customers')}}
                            </option>
                        </select>
                        @error('users')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label
                        class="col-form-label col-lg-1">{{__('pages/advertisements/create.content.inputs.categories')}}</label>
                    <div class="col-lg-11">
                        @error('categories')
                        <div class="invalid-feedback d-block mb-2" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                        <div class="transfer" id="categories_listbox"></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="name">{{__('pages/advertisements/create.content.inputs.countries')}}</label>
                    <div class="col-lg-11">
                        @error('countries')
                        <div class="invalid-feedback d-block mb-2" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                        <div class="transfer" id="countries"></div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="starts_at">{{__('pages/advertisements/create.content.inputs.starts_at')}}</label>
                    <div class="col-lg-11">
                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror"
                               id="starts_at"
                               name="starts_at"
                               value="{{old('starts_at') ?? \Carbon\Carbon::now()->format('Y-m-d\TH:i')}}">
                        @error('starts_at')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="ends_at">{{__('pages/advertisements/create.content.inputs.ends_at')}}</label>
                    <div class="col-lg-11">
                        <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror"
                               id="ends_at"
                               name="ends_at" value="{{old('ends_at')}}">
                        @error('ends_at')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="is_active">{{__('pages/advertisements/create.content.inputs.is_active')}}</label>
                    <div class="col-lg-11">
                        <select class="form-control @error('type') is-invalid @enderror" name="is_active" id="is_active"
                                required>
                            <option value="1" @if(old('is_active') === '1') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.boolean.yes')}}
                            </option>
                            <option value="0" @if(old('is_active') === '0') selected @endif>
                                {{__('pages/advertisements/create.content.inputs.boolean.no')}}
                            </option>
                        </select>
                        @error('is_active')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <hr>
                <div class="text-right">
                    <x-primary-button type="submit">
                        {{__('pages/advertisements/create.content.submit')}}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        let categoriesData = {!! json_encode($categories) !!};
        let countriesData = {!! json_encode($countries) !!};
        let categoriesSettings = {
            "tabNameText": "{{__('pages/advertisements/create.content.inputs.categories')}}",
            "rightTabNameText": "{{__('pages/advertisements/create.content.inputs.selected_categories')}}",
            "searchPlaceholderText": "{{__('pages/advertisements/create.content.inputs.placeholders.search')}}",
            "include": "{{__('pages/advertisements/create.content.inputs.include')}}",
            "exclude": "{{__('pages/advertisements/create.content.inputs.exclude')}}",
            "groupDataArray": categoriesData,
            "groupItemName": "CategoryName",
            "groupArrayName": "CategoryData",
            "itemName": "category",
            "valueName": "value",
            "inputName": 'categories',
            "selectedItems": {!! json_encode(old('categories')) !!},
        };
        let countriesSettings = {
            "tabNameText": "{{__('pages/advertisements/create.content.inputs.countries')}}",
            "rightTabNameText": "{{__('pages/advertisements/create.content.inputs.selected_cites')}}",
            "searchPlaceholderText": "{{__('pages/advertisements/create.content.inputs.placeholders.search')}}",
            "include": "{{__('pages/advertisements/create.content.inputs.include')}}",
            "exclude": "{{__('pages/advertisements/create.content.inputs.exclude')}}",
            "groupDataArray": countriesData,
            "groupItemName": "CountryName",
            "groupArrayName": "CountryData",
            "itemName": "city",
            "valueName": "value",
            "inputName": 'countries',
            "selectedItems": {!! json_encode(old('countries')) !!},
        };
        $("#categories_listbox").transfer(categoriesSettings);
        $("#countries").transfer(countriesSettings);
    </script>
@endpush
