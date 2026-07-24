<div class="card">
    <div class="card-header">
        @if($page_id)
            <h5 class="card-title">{!! __('pages/pages/edit.content.title', ['id' => $page_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/pages/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($page_id)
            @livewire('pages.pages-show-component', ['page_id' => $page_id], key($page_id))
        @else
            @livewire('pages.pages-inquiry-component')
        @endif
    </div>
</div>

