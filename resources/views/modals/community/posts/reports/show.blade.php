<!-- Edit Items Confirmation Modal -->
<x-dialog-modal wire:model="showMoreModal">
    <x-slot name="title">
        {{ $showMoreModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.post_id')}}
            <code>{{$log['post_id'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.type')}}
            <code>{{(isset($log['type']) && $log['type']) ?__("pages/community/posts/reports/show.modal.show.content.types.{$log['type']}"): null}}</code>
        </h4>
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.user_type')}}
            <code>{{$log['user_type'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.user_id')}}
            <code>{{$log['user_id'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.user_name')}}
            <code>{{$log['user_name'] ?? null}}
            </code>
        </h4>
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.reason')}}
            <code>{{$log['reason'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/posts/reports/show.modal.show.content.created_at')}}
            <code>{{$log['created_at'] ?? null}}</code>
        </h4>
    </x-slot>

    <x-slot name="footer">
        <x-primary-button wire:loading.attr="disabled" wire:click="closeShowMoreModal">
            {{ $showMoreModalTexts['close'] }}
        </x-primary-button>
    </x-slot>
</x-dialog-modal>
<!-- /Edit Items Confirmation Modal -->

