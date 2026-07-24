@props(['disabled' => false, 'wire' => null, 'multiple' => false, 'id' => null, 'options' => []])

<select
    {!! $wire ? $attributes->merge(['wire:model.debounce.500' => $wire]) : '' !!} {{ $disabled ? 'disabled' : '' }}  id="{{ $id ? $id : 'select-form' }}" {{ $multiple ? 'multiple' : '' }} {!! $attributes->merge(['class' => 'form-control']) !!}>
    @forelse ($options as $key => $option)
        <option value="{{$key}}">{{$option}}</option>
    @empty
        <option>No Options</option>
    @endforelse
</select>
