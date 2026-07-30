@props([
    'href',
    'label',
    'value',
    'icon',
    'tone' => 'teal',
    'suffix' => null,
])

<a href="{{ $href }}" class="dash-stat dash-stat--{{ $tone }}">
    <span class="dash-stat__top">
        <span class="dash-stat__icon">
            <i class="{{ $icon }}"></i>
        </span>
        <span class="dash-stat__go" aria-hidden="true">
            <i class="icon-arrow-left8"></i>
        </span>
    </span>
    <span class="dash-stat__value">
        {{ $value }}@if($suffix)<small>{{ $suffix }}</small>@endif
    </span>
    <span class="dash-stat__label">{{ $label }}</span>
</a>
