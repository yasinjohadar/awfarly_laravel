<div class="mx-2">
    <button @cannot('customers.inquiry') disabled @endcannot  wire:click="$emitUp('setCustomerId', {{ $reported_id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
