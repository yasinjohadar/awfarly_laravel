<!-- Edit Items Confirmation Modal -->
<x-dialog-modal wire:model="showMoreModal">
    <x-slot name="title">
        {{ $showMoreModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <h4>{{__('pages/system/logs/index.modal.show.content.log_id')}}
            <code>{{$log['id'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/system/logs/index.modal.show.content.type')}}
            <code>{{$log['type'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/system/logs/index.modal.show.content.action')}}
            <code>{{$log['action'] ?? null}}</code>
        </h4>
        <hr>
        <h4>{{__('pages/system/logs/index.modal.show.content.admin_id')}}
            <code>{{$log['admin']['id'] ?? null}}
            </code>
        </h4>
        <h4>{{__('pages/system/logs/index.modal.show.content.admin_name')}}
            <code>{{$log['admin']['name'] ?? null}}</code>
        </h4>
        <hr>
        <h4>{{__('pages/system/logs/index.modal.show.content.data')}}
            <code>
                <pre>{{( isset($log['data'])) ? json_encode($log['data'], JSON_PRETTY_PRINT) : __('pages/system/logs/index.modal.show.content.no_data')}}</pre>
            </code>
        </h4>
    </x-slot>

    <x-slot name="footer">
        <x-primary-button wire:loading.attr="disabled" wire:click="closeShowMoreModal">
            {{ $showMoreModalTexts['close'] }}
        </x-primary-button>
    </x-slot>
</x-dialog-modal>
<!-- /Edit Items Confirmation Modal -->

