<div class="custom-control custom-switch d-flex justify-content-center">
    <input id="elite_{{ $id }}" type="checkbox" {{ $isElite ? 'checked' : '' }}
           @cannot('packages.edit') disabled @endcannot
           wire:click="toggleElite({{ $id }})"
           class="custom-control-input" />
    <label class="custom-control-label" for="elite_{{ $id }}"></label>
</div>
