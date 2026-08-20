<div class="d-flex justify-content-around align-items-center" style="gap: .35rem">
    @if($deleted_at)
        <span class="badge badge-danger">
            {{ __('pages/categories/show.content.advertisers.datatable.deleted') }}
        </span>
    @endif
    @can('advertisers.inquiry')
        <a href="{{ route('admin.advertisers.show', $id) }}" target="_blank" class="btn btn-secondary btn-sm"
           title="{{ __('pages/categories/show.content.advertisers.actions.view') }}">
            <i class="icon-eye"></i>
        </a>
    @endcan
</div>
