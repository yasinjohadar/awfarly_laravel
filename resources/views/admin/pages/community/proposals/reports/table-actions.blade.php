<div class="mx-2">
    <button @cannot('proposals.reported') disabled @endcannot  wire:click="$emitUp('setProposalId', {{ $reported_id }})"
            class="btn btn-secondary">
        <i class="icon-folder-open"></i>
    </button>
</div>
