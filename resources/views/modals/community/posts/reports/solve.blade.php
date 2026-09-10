<x-confirmation-modal wire:model="showSolveModal" type="restore">
    <x-slot name="title">
        {{ $solveModalTexts['title'] }}
    </x-slot>

    <x-slot name="content">
        <p>{{ $solveModalTexts['content'] }}</p>

        <div class="form-group mb-0">
            <label>{{ __('pages/community/posts/reports/show.modal.solve.resolution_label') }}</label>
            <textarea class="form-control @error('resolution') is-invalid @enderror"
                      rows="3"
                      wire:model.defer="resolution"
                      placeholder="{{ __('pages/community/posts/reports/show.modal.solve.resolution_placeholder') }}"></textarea>
            @error('resolution')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('showSolveModal')" wire:loading.attr="disabled">
            {{ $solveModalTexts['cancel'] }}
        </x-secondary-button>

        <x-primary-button wire:loading.attr="disabled" wire:click="solve()">
            {{ $solveModalTexts['submit'] }}
        </x-primary-button>
    </x-slot>
</x-confirmation-modal>
