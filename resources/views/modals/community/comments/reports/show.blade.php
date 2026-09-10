<!-- Edit Items Confirmation Modal -->
<x-dialog-modal wire:model="showMoreModal">
    <x-slot name="title">
        {{ $showMoreModalTexts['title'] }}
    </x-slot>
    <x-slot name="content">
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.comment_id')}}
            <code>{{$log['comment_id'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.type')}}
            <code>{{(isset($log['type']) && $log['type']) ?__("pages/community/comments/reports/show.modal.show.content.types.{$log['type']}"): null}}</code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.user_type')}}
            <code>{{$log['user_type'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.user_id')}}
            <code>{{$log['user_id'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.user_name')}}
            <code>{{$log['user_name'] ?? null}}
            </code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.reason')}}
            <code>{{$log['reason'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.modal.show.content.created_at')}}
            <code>{{$log['created_at'] ?? null}}</code>
        </h4>
        <h4>{{__('pages/community/comments/reports/show.content.status')}}
            <code>{{($log['status'] ?? null) === 'solved' ? __('pages/community/comments/reports/show.content.solved') : __('pages/community/comments/reports/show.content.unsolved')}}</code>
        </h4>
        @if(($log['status'] ?? null) === 'solved' && !empty($log['resolution']))
            <h4>{{__('pages/community/comments/reports/show.content.resolution')}}
                <code>{{$log['resolution']}}</code>
            </h4>
            @if(!empty($log['resolved_by']))
                <h4>{{__('pages/community/comments/reports/show.content.resolved_by')}}
                    <code>{{$log['resolved_by']}}</code>
                </h4>
            @endif
            @if(!empty($log['resolved_at']))
                <h4>{{__('pages/community/comments/reports/show.content.resolved_at')}}
                    <code>{{$log['resolved_at']}}</code>
                </h4>
            @endif
        @endif
    </x-slot>

    <x-slot name="footer">
        <x-primary-button wire:loading.attr="disabled" wire:click="closeShowMoreModal">
            {{ $showMoreModalTexts['close'] }}
        </x-primary-button>
    </x-slot>
</x-dialog-modal>
<!-- /Edit Items Confirmation Modal -->

