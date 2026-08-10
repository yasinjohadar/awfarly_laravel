<details class="advertiser-row-actions">
    <style>
        .advertiser-row-actions {
            position: relative;
            display: inline-block;
        }

        .advertiser-row-actions > summary {
            list-style: none;
            cursor: pointer;
            user-select: none;
        }

        .advertiser-row-actions > summary::-webkit-details-marker {
            display: none;
        }

        .advertiser-row-actions[open] > summary {
            background: #eef2f7;
        }

        .advertiser-row-actions__menu {
            position: absolute;
            top: calc(100% + .25rem);
            inset-inline-end: 0;
            z-index: 1060;
            min-width: 11.5rem;
            max-width: 15rem;
            width: max-content;
            padding: .35rem 0;
            background: #fff;
            border: 1px solid #e3e8ef;
            border-radius: .4rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }

        .advertiser-row-actions__menu button {
            display: flex;
            align-items: center;
            width: 100%;
            border: 0;
            background: transparent;
            padding: .5rem .85rem;
            color: #37474f;
            font-size: .875rem;
            line-height: 1.35;
            white-space: nowrap;
            text-align: start;
            cursor: pointer;
        }

        .advertiser-row-actions__menu button:hover {
            background: #f3f6f9;
        }

        .advertiser-row-actions__menu button.text-danger {
            color: #e53935;
        }

        .advertiser-row-actions__menu .divider {
            height: 0;
            margin: .3rem 0;
            border-top: 1px solid #eef2f6;
        }

        .advertiser-row-actions__menu i {
            width: 1.1rem;
            text-align: center;
        }

        .advertiser-row-actions__menu .action-label {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.25;
        }

        .advertiser-row-actions__menu .action-hint {
            font-size: .7rem;
            color: #90a4ae;
            white-space: normal;
        }
    </style>

    <summary class="btn btn-light border btn-sm">
        <i class="icon-menu7 mr-1"></i>
        {{ __('datatable.actions') }}
        <i class="icon-arrow-down22 ml-1" style="font-size:.7rem"></i>
    </summary>

    <div class="advertiser-row-actions__menu">
        @if(!$deleted_at)
            @can('advertisers.edit')
                <button type="button" wire:click="showAssignPackageModal({{ $id }})">
                    <i class="icon-stack2 mr-2 text-primary"></i>
                    {{ __('pages/advertisers/index.modal.assign_package.title') }}
                </button>
                <button type="button" wire:click="showEditModal({{ $id }})">
                    <i class="icon-pencil7 mr-2 text-secondary"></i>
                    {{ __('pages/advertisers/index.actions.edit') }}
                </button>
                <button type="button" wire:click="showCategoriesModal({{ $id }})">
                    <i class="icon-stack2 mr-2 text-secondary"></i>
                    {{ __('pages/advertisers/index.actions.view_categories') }}
                </button>
                <div class="divider"></div>
                @if($status === 'active')
                    <button type="button" wire:click="showStatusModal({{ $id }}, 'inactive')">
                        <i class="icon-pause mr-2 text-warning"></i>
                        <span class="action-label">
                            {{ __('pages/advertisers/index.actions.stop') }}
                            <span class="action-hint">{{ __('pages/advertisers/index.actions.stop_hint') }}</span>
                        </span>
                    </button>
                    <button type="button" wire:click="showStatusModal({{ $id }}, 'banned')">
                        <i class="icon-lock2 mr-2 text-dark"></i>
                        <span class="action-label">
                            {{ __('pages/advertisers/index.actions.freeze') }}
                            <span class="action-hint">{{ __('pages/advertisers/index.actions.freeze_hint') }}</span>
                        </span>
                    </button>
                @elseif($status === 'inactive')
                    <button type="button" wire:click="changeStatus({{ $id }}, 'active')">
                        <i class="icon-checkmark3 mr-2 text-success"></i>
                        {{ __('pages/advertisers/index.actions.activate') }}
                    </button>
                    <button type="button" wire:click="showStatusModal({{ $id }}, 'banned')">
                        <i class="icon-lock2 mr-2 text-dark"></i>
                        <span class="action-label">
                            {{ __('pages/advertisers/index.actions.freeze') }}
                            <span class="action-hint">{{ __('pages/advertisers/index.actions.freeze_hint') }}</span>
                        </span>
                    </button>
                @elseif($status === 'banned')
                    <button type="button" wire:click="changeStatus({{ $id }}, 'active')">
                        <i class="icon-checkmark3 mr-2 text-success"></i>
                        {{ __('pages/advertisers/index.actions.activate') }}
                    </button>
                @endif
            @endcan

            @can('advertisers.delete')
                <div class="divider"></div>
                <button type="button" class="text-danger" wire:click="showDeleteModal({{ $id }})">
                    <i class="icon-trash mr-2"></i>
                    {{ __('pages/advertisers/index.actions.delete') }}
                </button>
            @endcan
        @else
            @can('advertisers.edit')
                <button type="button" wire:click="restoreAdvertiser({{ $id }})">
                    <i class="icon-history mr-2 text-success"></i>
                    {{ __('pages/advertisers/index.actions.restore') }}
                </button>
            @endcan
        @endif
    </div>
</details>
