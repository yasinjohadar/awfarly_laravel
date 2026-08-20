@php($filters = 'pages/categories/show.content.advertisers.filters')

<div class="cat-adv-filters">
    <style>
        .cat-adv-filters {
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f6f8fb;
            border: 1px solid #dde5ee;
            border-radius: .75rem;
        }

        .cat-adv-filters__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: .85rem;
        }

        .cat-adv-filters__title {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: #263238;
        }

        .cat-adv-filters__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        @media (max-width: 991px) {
            .cat-adv-filters__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .cat-adv-filters__grid {
                grid-template-columns: 1fr;
            }
        }

        .cat-adv-filters__label {
            display: block;
            margin-bottom: .25rem;
            font-size: .75rem;
            font-weight: 600;
            color: #607d8b;
            text-transform: uppercase;
        }
    </style>

    <div class="cat-adv-filters__head">
        <h6 class="cat-adv-filters__title">
            <i class="icon-filter3"></i>
            {{ __("$filters.title") }}
        </h6>
        <div class="d-flex align-items-center flex-wrap" style="gap: .5rem">
            <span class="badge badge-primary p-2">
                {{ __("$filters.results", ['count' => $this->results->total()]) }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="resetFilters">
                <i class="icon-reset mr-1"></i>
                {{ __("$filters.reset") }}
            </button>
        </div>
    </div>

    <div class="cat-adv-filters__grid">
        @if($sub_categories->isNotEmpty())
            <div>
                <label class="cat-adv-filters__label" for="cat-adv-scope">{{ __("$filters.scope") }}</label>
                <select id="cat-adv-scope" class="form-control" wire:model="scope"
                        @if($sub_category_id) disabled @endif>
                    <option value="all">{{ __("$filters.scope_all") }}</option>
                    <option value="direct">{{ __("$filters.scope_direct") }}</option>
                    <option value="subs">{{ __("$filters.scope_subs") }}</option>
                </select>
            </div>

            <div>
                <label class="cat-adv-filters__label"
                       for="cat-adv-sub-category">{{ __("$filters.sub_category") }}</label>
                <select id="cat-adv-sub-category" class="form-control" wire:model="sub_category_id">
                    <option value="">{{ __("$filters.sub_category_all") }}</option>
                    @foreach($sub_categories as $sub_category)
                        <option value="{{ $sub_category['id'] }}">{{ $sub_category['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="cat-adv-filters__label" for="cat-adv-status">{{ __("$filters.status") }}</label>
            <select id="cat-adv-status" class="form-control" wire:model="status_filter">
                <option value="">{{ __("$filters.status_all") }}</option>
                @foreach($this->all_statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="cat-adv-filters__label" for="cat-adv-elite">{{ __("$filters.elite") }}</label>
            <select id="cat-adv-elite" class="form-control" wire:model="elite_filter">
                <option value="">{{ __("$filters.elite_all") }}</option>
                <option value="1">{{ __("$filters.elite_only") }}</option>
                <option value="0">{{ __("$filters.elite_none") }}</option>
            </select>
        </div>

        @if($packages->isNotEmpty())
            <div>
                <label class="cat-adv-filters__label" for="cat-adv-package">{{ __("$filters.package") }}</label>
                <select id="cat-adv-package" class="form-control" wire:model="package_filter">
                    <option value="">{{ __("$filters.package_all") }}</option>
                    <option value="none">{{ __("$filters.package_none") }}</option>
                    @foreach($packages as $package)
                        <option value="{{ $package['id'] }}">{{ $package['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="cat-adv-filters__label" for="cat-adv-trashed">{{ __("$filters.trashed") }}</label>
            <select id="cat-adv-trashed" class="form-control" wire:model="trashed_filter">
                <option value="without">{{ __("$filters.trashed_without") }}</option>
                <option value="with">{{ __("$filters.trashed_with") }}</option>
                <option value="only">{{ __("$filters.trashed_only") }}</option>
            </select>
        </div>
    </div>
</div>
