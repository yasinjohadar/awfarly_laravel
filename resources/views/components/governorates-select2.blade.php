@props(['governorates' => null])
<div class="form-group row" x-data="{governorate_id: @entangle('governorate_id').defer, country_governorates: {{$governorates}},}"
     x-subscribe="country_governorates"
     x-init="$nextTick(() => {select2 = $($refs.select).select2().val('').change();select2.on('select2:select', (event) => {governorate_id = $('#governorate_id').val(); });})">
    <label class="col-form-label col-lg-2"
           for="governorate_id">{{__('pages/advertisers/create.content.inputs.governorate')}}</label>
    <div class="col-lg-10">
        <select x-cloak x-model="governorate_id" name="governorate_id"
                id="governorate_id"
                class="form-control select2 @error('governorate_id') is-invalid @enderror"
                x-ref="select"
                x-bind:value="governorate_id">
            <template x-for="[id, value] in Object.entries(country_governorates)">
                <option :value="value.code" x-text="value.value"></option>
            </template>
        </select>
        @error('governorate_id')
        <div class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </div>
        @enderror
    </div>
</div>
