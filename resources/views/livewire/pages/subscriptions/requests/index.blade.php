<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('pages/subscriptions/requests/index.content.title') }}</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight mb-3">
                <li class="nav-item">
                    <a href="javascript:void(0)" wire:click="changeActiveTab('pending')"
                       class="nav-link {{ $page_type === 'pending' ? 'active' : '' }}">
                        {{ __('pages/subscriptions/requests/index.content.tabs.pending') }} ({{ $pending_count }})
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" wire:click="changeActiveTab('approved')"
                       class="nav-link {{ $page_type === 'approved' ? 'active' : '' }}">
                        {{ __('pages/subscriptions/requests/index.content.tabs.approved') }} ({{ $approved_count }})
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" wire:click="changeActiveTab('rejected')"
                       class="nav-link {{ $page_type === 'rejected' ? 'active' : '' }}">
                        {{ __('pages/subscriptions/requests/index.content.tabs.rejected') }} ({{ $rejected_count }})
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" wire:click="changeActiveTab('all')"
                       class="nav-link {{ $page_type === 'all' ? 'active' : '' }}">
                        {{ __('pages/subscriptions/requests/index.content.tabs.all') }} ({{ $all_count }})
                    </a>
                </li>
            </ul>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('pages/subscriptions/requests/index.content.table.advertiser') }}</th>
                        <th>{{ __('pages/subscriptions/requests/index.content.table.package') }}</th>
                        <th>{{ __('pages/subscriptions/requests/index.content.table.status') }}</th>
                        <th>{{ __('pages/subscriptions/requests/index.content.table.receipt') }}</th>
                        <th>{{ __('pages/subscriptions/requests/index.content.table.created_at') }}</th>
                        <th>{{ __('pages/subscriptions/requests/index.content.table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                {{ $item->advertiser->name ?? ('#'.$item->advertiser_id) }}
                            </td>
                            <td>
                                @php
                                    $nameCol = app()->getLocale() === 'ar' ? 'name_ar' : 'name_en';
                                @endphp
                                {{ optional($item->package)->{$nameCol} ?? ('#'.$item->package_id) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->status === 'pending' ? 'warning' : ($item->status === 'approved' ? 'success' : 'danger') }}">
                                    {{ __('pages/subscriptions/requests/index.content.status.'.$item->status) }}
                                </span>
                            </td>
                            <td>
                                @if($item->receipt)
                                    <a href="{{ route('admin.subscriptions.requests.receipt', $item->id) }}"
                                       target="_blank" class="btn btn-sm btn-secondary">
                                        {{ __('pages/subscriptions/requests/index.content.actions.view_receipt') }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($item->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-success"
                                            wire:click="approve({{ $item->id }})"
                                            wire:loading.attr="disabled">
                                        {{ __('pages/subscriptions/requests/index.content.actions.approve') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            wire:click="reject({{ $item->id }})"
                                            wire:loading.attr="disabled">
                                        {{ __('pages/subscriptions/requests/index.content.actions.reject') }}
                                    </button>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                {{ __('pages/subscriptions/requests/index.content.empty') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </div>
</div>
