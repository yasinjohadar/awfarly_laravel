@props(['cities' => null])
<div class="form-group row" x-data="{city_id: @entangle('city_id').defer, governorate_cities: {{$cities}},}"
     x-subscribe="governorate_cities"
     x-init="$nextTick(() => {select2 = $($refs.select).select2().val('').change();select2.on('select2:select', (event) => {city_id = $('#city_id').val(); });})">
    <label class="col-form-label col-lg-2"
           for="city_id">{{__('pages/advertisers/create.content.inputs.city')}}</label>
    <div class="col-lg-10">
        <select x-cloak x-model="city_id" name="city_id"
                id="city_id"
                class="form-control select2 @error('city_id') is-invalid @enderror"
                x-ref="select"
                x-bind:value="city_id">
            <template x-for="[id, value] in Object.entries(governorate_cities)">
                <option :value="value.code" x-text="value.value"></option>
            </template>
        </select>
        @error('city_id')
        <div class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </div>
        @enderror
    </div>
</div>
