<div>
    <div class="form-group" x-data="{show: false}">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setCommentId', null)">{{__('pages/community/comments/reports/show.content.back')}}</button>
        <button title="Edit" @cannot('comments.reported') disabled
                @endcannot  wire:click="showSolveModal({{ $comment_id }})"
                class="btn btn-primary mx-1">
            {{__('pages/community/comments/reports/show.content.solve')}}
        </button>
        @if(!$comment->deleted_at)
            <button title="Edit" @cannot('comments.reported') disabled
                    @endcannot  wire:click="showDeleteModal({{ $comment_id }})"
                    class="btn btn-danger mx-1">
                {{__('pages/community/comments/reports/show.content.delete')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/comments/reports/show.content.comment_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$comment->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/comments/reports/show.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($comment->user->user_type)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/comments/reports/show.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$comment->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/comments/reports/show.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$comment->user->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/comments/reports/show.content.deleted_at')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$comment->deleted_at ? \Carbon\Carbon::make($comment->deleted_at)->format('Y-m-d h:i A') : '-'}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/comments/reports/show.content.status')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{($comment->deleted_at) ? __('pages/community/comments/reports/show.content.solved') : __('pages/community/comments/reports/show.content.unsolved')}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/comments/reports/show.content.content')}}</div>
                <div class="text-secondary">
                    {!! $comment->comment !!}
                </div>
            </div>
        </div>
    </div>
    {{--@include('modals.community.comments.answers.edit')--}}
    @livewire('community.comments.reports.community-reported-comment-show-component', ['comment_id' => $comment_id], key($comment_id))
    @include('modals.community.comments.reports.delete')
    @include('modals.community.comments.reports.solve')
</div>

