@props([
    'href',
    'label',
    'value',
    'icon',
    'tone' => 'amber',
])

@php
    $count = is_numeric($value) ? (int) $value : (int) preg_replace('/[^\d]/', '', (string) $value);
    $isEmpty = $count <= 0;
@endphp

<a href="{{ $href }}" class="dash-alert-stat dash-alert-stat--{{ $tone }}{{ $isEmpty ? ' is-empty' : '' }}">
    <span class="dash-alert-stat__icon">
        <i class="{{ $icon }}"></i>
    </span>
    <span class="dash-alert-stat__value">{{ $value }}</span>
    <span class="dash-alert-stat__label">{{ $label }}</span>
</a>
