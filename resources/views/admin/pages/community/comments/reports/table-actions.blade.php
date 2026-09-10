<div class="d-flex justify-content-around align-items-center">
    <div class="mx-1">
        <button @cannot('comments.reported') disabled @endcannot
                wire:click="$emitUp('setCommentId', {{ $reported_id }})"
                class="btn btn-secondary"
                title="{{ __('pages/community/comments/reports/reports.content.datatable.action_view') }}">
            <i class="icon-folder-open"></i>
        </button>
    </div>
    <div class="mx-1">
        <button @cannot('comments.delete') disabled @endcannot
                wire:click="showDeletePostModal({{ $reported_id }})"
                class="btn btn-danger"
                title="{{ __('pages/community/comments/reports/reports.content.datatable.action_delete_post') }}">
            <i class="icon-file-minus"></i>
        </button>
    </div>
    <div class="mx-1">
        <button @cannot('comments.reported') disabled @endcannot
                wire:click="showDeleteModal({{ $reported_id }})"
                class="btn btn-warning"
                title="{{ __('pages/community/comments/reports/reports.content.datatable.action_delete_reports') }}">
            <i class="icon-trash"></i>
        </button>
    </div>
</div>
