<div class="package-show" wire:key="package-show-{{ $package_id }}">
    <style>
        .package-show {
            --ps-accent: #2196f3;
            --ps-accent-soft: rgba(33, 150, 243, 0.08);
            --ps-success: #43a047;
            --ps-warning: #fb8c00;
            --ps-muted: #78909c;
            --ps-card: #fff;
            --ps-border: #e3e8ef;
            --ps-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .package-show__toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.25rem;
        }

        .package-show__hero {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 45%),
                linear-gradient(135deg, #1e88e5 0%, #1565c0 55%, #0d47a1 100%);
            box-shadow: var(--ps-shadow);
        }

        .package-show__hero::after {
            content: "";
            position: absolute;
            inset: auto -2rem -3rem auto;
            width: 12rem;
            height: 12rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .package-show__hero-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .package-show__names h3 {
            margin: 0 0 .35rem;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .package-show__names .secondary-name {
            margin: 0;
            opacity: .85;
            font-size: 1.05rem;
        }

        .package-show__product {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .85rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: .82rem;
            backdrop-filter: blur(4px);
        }

        .package-show__price-block {
            text-align: end;
            min-width: 10rem;
        }

        .package-show__price {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .package-show__price-period {
            opacity: .85;
            font-size: .9rem;
        }

        .package-show__old-price {
            margin-top: .25rem;
            opacity: .7;
            text-decoration: line-through;
            font-size: .9rem;
        }

        .package-show__badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .package-show__badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: transform .2s ease, background .2s ease;
        }

        .package-show__badge:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.26);
        }

        .package-show__badge.is-off {
            opacity: .55;
        }

        .package-show__metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .package-show__metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .package-show__metrics {
                grid-template-columns: 1fr;
            }

            .package-show__price-block {
                text-align: start;
            }
        }

        .package-show__metric {
            background: var(--ps-card);
            border: 1px solid var(--ps-border);
            border-radius: .9rem;
            padding: 1rem 1.1rem;
            box-shadow: var(--ps-shadow);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            cursor: default;
        }

        .package-show__metric:hover {
            transform: translateY(-4px);
            border-color: rgba(33, 150, 243, 0.35);
            box-shadow: 0 14px 28px rgba(33, 150, 243, 0.12);
        }

        .package-show__metric-label {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: var(--ps-muted);
            font-size: .82rem;
            margin-bottom: .45rem;
        }

        .package-show__metric-label i {
            color: var(--ps-accent);
            font-size: 1rem;
        }

        .package-show__metric-value {
            font-size: 1.65rem;
            font-weight: 800;
            color: #1a237e;
            line-height: 1;
        }

        .package-show__section {
            background: var(--ps-card);
            border: 1px solid var(--ps-border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            margin-bottom: 1.25rem;
            box-shadow: var(--ps-shadow);
        }

        .package-show__section-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .package-show__section-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #263238;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .package-show__section-title::before {
            content: "";
            width: .35rem;
            height: 1.1rem;
            border-radius: 999px;
            background: var(--ps-accent);
        }

        .package-show__lang-toggle {
            display: inline-flex;
            padding: .2rem;
            border-radius: 999px;
            background: #f1f5f9;
            border: 1px solid var(--ps-border);
        }

        .package-show__lang-toggle button {
            border: 0;
            background: transparent;
            border-radius: 999px;
            padding: .35rem .85rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--ps-muted);
            transition: all .2s ease;
        }

        .package-show__lang-toggle button.active {
            background: #fff;
            color: var(--ps-accent);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        }

        .package-show__features {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .75rem;
        }

        .package-show__feature {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .9rem 1rem;
            border-radius: .85rem;
            border: 1px solid var(--ps-border);
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .package-show__feature:hover {
            transform: translateY(-3px) scale(1.01);
            border-color: rgba(33, 150, 243, 0.4);
            box-shadow: 0 10px 22px rgba(33, 150, 243, 0.1);
        }

        .package-show__feature-icon {
            flex: 0 0 2.1rem;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: .65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--ps-accent-soft);
            color: var(--ps-accent);
            font-size: .95rem;
        }

        .package-show__feature-text {
            font-weight: 600;
            color: #37474f;
            line-height: 1.45;
            margin: 0;
        }

        .package-show__empty {
            padding: 1.25rem;
            text-align: center;
            color: var(--ps-muted);
            border: 1px dashed var(--ps-border);
            border-radius: .85rem;
            background: #fafbfc;
        }

        .package-show__descriptions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 767px) {
            .package-show__descriptions {
                grid-template-columns: 1fr;
            }
        }

        .package-show__desc-card {
            border: 1px solid var(--ps-border);
            border-radius: .85rem;
            padding: 1rem 1.1rem;
            background: #fafbfc;
            transition: border-color .2s ease, box-shadow .2s ease;
            min-height: 8rem;
        }

        .package-show__desc-card:hover {
            border-color: rgba(33, 150, 243, 0.35);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .package-show__desc-label {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .65rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            background: var(--ps-accent-soft);
            color: var(--ps-accent);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .package-show__desc-body {
            color: #546e7a;
            line-height: 1.7;
            white-space: pre-line;
        }

        .package-show__meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .75rem;
        }

        .package-show__meta-item {
            padding: .85rem 1rem;
            border-radius: .85rem;
            border: 1px solid var(--ps-border);
            background: #fff;
            transition: transform .2s ease, border-color .2s ease;
        }

        .package-show__meta-item:hover {
            transform: translateY(-2px);
            border-color: rgba(33, 150, 243, 0.3);
        }

        .package-show__meta-item span {
            display: block;
            color: var(--ps-muted);
            font-size: .78rem;
            margin-bottom: .25rem;
        }

        .package-show__meta-item strong {
            color: #263238;
            font-size: .95rem;
        }

        .package-show__subscribers-table-wrap {
            overflow-x: auto;
        }

        .package-show__subscribers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .package-show__subscribers-table th,
        .package-show__subscribers-table td {
            padding: .65rem .75rem;
            border-bottom: 1px solid var(--ps-border);
            text-align: start;
            white-space: nowrap;
        }

        .package-show__subscribers-table th {
            color: var(--ps-muted);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .package-show__status-pill {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .6rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
        }

        .package-show__status-pill.is-current {
            background: rgba(67, 160, 71, 0.12);
            color: var(--ps-success);
        }

        .package-show__status-pill.is-ended {
            background: rgba(120, 144, 156, 0.14);
            color: var(--ps-muted);
        }
    </style>

    <div class="package-show__toolbar">
        <button class="btn btn-secondary" wire:click="$emitUp('setPackageId', null)">
            <i class="icon-arrow-right13 mr-1"></i>
            {{ __('pages/subscriptions/packages/show.content.back') }}
        </button>
        @can('packages.edit')
            <a title="Edit" href="{{ route('admin.subscriptions.packages.edit', $package_id) }}" class="btn btn-primary">
                <i class="icon-pencil7 mr-1"></i>
                {{ __('pages/subscriptions/packages/show.content.edit') }}
            </a>
        @else
            <button title="Edit" disabled class="btn btn-primary">
                <i class="icon-pencil7 mr-1"></i>
                {{ __('pages/subscriptions/packages/show.content.edit') }}
            </button>
        @endcan
    </div>

    <div class="package-show__hero">
        <div class="package-show__hero-top">
            <div class="package-show__names">
                <h3>{{ app()->getLocale() === 'ar' ? $package->name_ar : $package->name_en }}</h3>
                <p class="secondary-name">
                    {{ app()->getLocale() === 'ar' ? $package->name_en : $package->name_ar }}
                </p>
                @if($package->product_id)
                    <div class="package-show__product">
                        <i class="icon-qrcode"></i>
                        <span>{{ $package->product_id }}</span>
                    </div>
                @endif
            </div>

            <div class="package-show__price-block">
                <div class="package-show__price">
                    {{ number_format((float) $package->price, 2) }}
                    <small>{{ $package->currency }}</small>
                </div>
                <div class="package-show__price-period">
                    / {{ __("pages/subscriptions/packages/show.content.duration_types.{$package->subscription_type}") }}
                </div>
                @if(!is_null($package->old_price) && (float) $package->old_price > 0)
                    <div class="package-show__old-price">
                        {{ number_format((float) $package->old_price, 2) }} {{ $package->currency }}
                    </div>
                @endif
            </div>
        </div>

        <div class="package-show__badges">
            <span class="package-show__badge {{ $package->is_active ? '' : 'is-off' }}">
                <i class="{{ $package->is_active ? 'icon-checkmark3' : 'icon-cross2' }}"></i>
                {{ __('pages/subscriptions/packages/show.content.is_active') }}:
                {{ $package->is_active ? __('pages/subscriptions/packages/show.content.boolean.yes') : __('pages/subscriptions/packages/show.content.boolean.no') }}
            </span>
            <span class="package-show__badge {{ $package->is_visible ? '' : 'is-off' }}">
                <i class="{{ $package->is_visible ? 'icon-eye' : 'icon-eye-blocked' }}"></i>
                {{ __('pages/subscriptions/packages/show.content.is_visible') }}:
                {{ $package->is_visible ? __('pages/subscriptions/packages/show.content.boolean.yes') : __('pages/subscriptions/packages/show.content.boolean.no') }}
            </span>
            <span class="package-show__badge {{ $package->is_trial ? '' : 'is-off' }}">
                <i class="icon-gift"></i>
                {{ __('pages/subscriptions/packages/show.content.is_trial') }}:
                {{ $package->is_trial ? __('pages/subscriptions/packages/show.content.boolean.yes') : __('pages/subscriptions/packages/show.content.boolean.no') }}
            </span>
        </div>
    </div>

    <div class="package-show__metrics">
        <div class="package-show__metric">
            <div class="package-show__metric-label">
                <i class="icon-file-text2"></i>
                {{ __('pages/subscriptions/packages/show.content.maximum_posts') }}
            </div>
            <div class="package-show__metric-value">{{ $package->maximum_posts ?? 0 }}</div>
        </div>
        <div class="package-show__metric">
            <div class="package-show__metric-label">
                <i class="icon-stack2"></i>
                {{ __('pages/subscriptions/packages/show.content.maximum_offers') }}
            </div>
            <div class="package-show__metric-value">{{ $package->maximum_offers ?? 0 }}</div>
        </div>
        <div class="package-show__metric">
            <div class="package-show__metric-label">
                <i class="icon-calendar52"></i>
                {{ __('pages/subscriptions/packages/show.content.maximum_monthly_offers') }}
            </div>
            <div class="package-show__metric-value">{{ $package->maximum_monthly_offers ?? 0 }}</div>
        </div>
        <div class="package-show__metric">
            <div class="package-show__metric-label">
                <i class="icon-watch2"></i>
                {{ __('pages/subscriptions/packages/show.content.duration') }}
            </div>
            <div class="package-show__metric-value">{{ $package->duration ?? '-' }}</div>
        </div>
    </div>

    <div class="package-show__section">
        <div class="package-show__section-head">
            <h5 class="package-show__section-title">
                {{ __('pages/subscriptions/packages/show.content.features_title') }}
            </h5>
            <div class="package-show__lang-toggle" role="group"
                 aria-label="{{ __('pages/subscriptions/packages/show.content.features_lang') }}">
                <button type="button"
                        class="{{ $features_lang === 'ar' ? 'active' : '' }}"
                        wire:click="setFeaturesLang('ar')">
                    {{ __('pages/subscriptions/packages/show.content.lang_ar') }}
                </button>
                <button type="button"
                        class="{{ $features_lang === 'en' ? 'active' : '' }}"
                        wire:click="setFeaturesLang('en')">
                    {{ __('pages/subscriptions/packages/show.content.lang_en') }}
                </button>
            </div>
        </div>

        @php
            $features = $features_lang === 'ar'
                ? ($package->specifications_ar ?? [])
                : ($package->specifications_en ?? []);
            $features = is_array($features) ? array_values(array_filter($features)) : [];
        @endphp

        @if(count($features))
            <div class="package-show__features" wire:key="features-{{ $features_lang }}">
                @foreach($features as $feature)
                    <div class="package-show__feature">
                        <span class="package-show__feature-icon">
                            <i class="icon-checkmark3"></i>
                        </span>
                        <p class="package-show__feature-text">{{ $feature }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="package-show__empty">
                {{ __('pages/subscriptions/packages/show.content.no-specifications') }}
            </div>
        @endif
    </div>

    <div class="package-show__section">
        <div class="package-show__section-head">
            <h5 class="package-show__section-title">
                {{ __('pages/subscriptions/packages/show.content.description_title') }}
            </h5>
        </div>
        <div class="package-show__descriptions">
            <div class="package-show__desc-card">
                <div class="package-show__desc-label">AR</div>
                <div class="package-show__desc-body">
                    {!! $package->description_ar ?: e(__('pages/subscriptions/packages/show.content.no_description')) !!}
                </div>
            </div>
            <div class="package-show__desc-card">
                <div class="package-show__desc-label">EN</div>
                <div class="package-show__desc-body">
                    {!! $package->description_en ?: e(__('pages/subscriptions/packages/show.content.no_description')) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="package-show__section">
        <div class="package-show__section-head">
            <h5 class="package-show__section-title">
                {{ __('pages/subscriptions/packages/show.content.details_title') }}
            </h5>
        </div>
        <div class="package-show__meta">
            <div class="package-show__meta-item">
                <span>{{ __('pages/subscriptions/packages/show.content.name_ar') }}</span>
                <strong>{{ $package->name_ar }}</strong>
            </div>
            <div class="package-show__meta-item">
                <span>{{ __('pages/subscriptions/packages/show.content.name_en') }}</span>
                <strong>{{ $package->name_en }}</strong>
            </div>
            <div class="package-show__meta-item">
                <span>{{ __('pages/subscriptions/packages/show.content.product_id') }}</span>
                <strong>{{ $package->product_id ?: '-' }}</strong>
            </div>
            <div class="package-show__meta-item">
                <span>{{ __('pages/subscriptions/packages/show.content.currency') }}</span>
                <strong>{{ $package->currency }}</strong>
            </div>
            <div class="package-show__meta-item">
                <span>{{ __('pages/subscriptions/packages/show.content.subscribers') }}</span>
                <strong>{{ $package->advertisers_count ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <div class="package-show__section">
        <div class="package-show__section-head">
            <h5 class="package-show__section-title">
                {{ __('pages/subscriptions/packages/show.content.subscribers_title') }}
            </h5>
            @can('packages.edit')
                <button type="button" class="btn btn-sm btn-primary" wire:click="openAssignAdvertiserModal">
                    <i class="icon-plus3 mr-1"></i>
                    {{ __('pages/subscriptions/packages/show.content.subscribers_table.add_advertiser') }}
                </button>
            @endcan
        </div>

        @if($subscriptions->count())
            <div class="package-show__subscribers-table-wrap">
                <table class="package-show__subscribers-table">
                    <thead>
                        <tr>
                            <th>{{ __('pages/subscriptions/packages/show.content.subscribers_table.advertiser') }}</th>
                            <th>{{ __('pages/subscriptions/packages/show.content.subscribers_table.status') }}</th>
                            <th>{{ __('pages/subscriptions/packages/show.content.subscribers_table.starts_at') }}</th>
                            <th>{{ __('pages/subscriptions/packages/show.content.subscribers_table.ends_at') }}</th>
                            <th>{{ __('pages/subscriptions/packages/show.content.subscribers_table.purchase_count') }}</th>
                            <th>{{ __('datatable.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            @php($isCurrent = $subscription->is_current && $subscription->is_active && !$subscription->is_ended)
                            <tr>
                                <td>
                                    @if($subscription->advertiser)
                                        {{ $subscription->advertiser->name }}
                                        <span class="text-muted">
                                            (#{{ $subscription->advertiser->id }} - {{ $subscription->advertiser->username }})
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            {{ __('pages/subscriptions/packages/show.content.subscribers_table.deleted_advertiser') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($isCurrent)
                                        <span class="package-show__status-pill is-current">
                                            <i class="icon-checkmark3"></i>
                                            {{ __('pages/subscriptions/packages/show.content.subscribers_table.current') }}
                                        </span>
                                    @else
                                        <span class="package-show__status-pill is-ended">
                                            <i class="icon-cross2"></i>
                                            {{ __('pages/subscriptions/packages/show.content.subscribers_table.ended') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ optional($subscription->starts_at)->format('Y-m-d') ?: '-' }}</td>
                                <td>{{ optional($subscription->ends_at)->format('Y-m-d') ?: '-' }}</td>
                                <td>{{ $subscription->purchase_count }}</td>
                                <td>
                                    @if($isCurrent)
                                        @can('packages.edit')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="openEndSubscriptionModal({{ $subscription->id }})">
                                                {{ __('pages/subscriptions/packages/show.content.subscribers_table.cancel_subscription') }}
                                            </button>
                                        @endcan
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $subscriptions->links() }}
            </div>
        @else
            <div class="package-show__empty">
                {{ __('pages/subscriptions/packages/show.content.subscribers_table.empty') }}
            </div>
        @endif
    </div>

    <x-form-modal wire:model="showAssignAdvertiserModal" type="add" wire="assignAdvertiserToPackage">
    <x-slot name="title">
        {{ __('pages/subscriptions/packages/show.content.assign_advertiser_modal.title') }}
    </x-slot>
    <x-slot name="content">
        <div class="form-group mb-0" wire:ignore
             x-data="{advertiser_id: @entangle('assign_advertiser_id').defer}"
             x-init="$nextTick(() => {
                 let select2 = $('#assign_advertiser_id').select2({
                    placeholder: '{{ __('pages/subscriptions/packages/show.content.assign_advertiser_modal.placeholder') }}',
                    allowClear: true,
                    dropdownParent: $('#assign_advertiser_id').closest('.modal')
                }).val(advertiser_id).change();
                select2.on('change', (event) => {
                    advertiser_id = event.target.value;
                });
            })">
            <label for="assign_advertiser_id">{{ __('pages/subscriptions/packages/show.content.assign_advertiser_modal.advertiser') }}</label>
            <select x-model="advertiser_id" x-cloak
                    data-placeholder="{{ __('pages/subscriptions/packages/show.content.assign_advertiser_modal.placeholder') }}"
                    id="assign_advertiser_id"
                    class="form-control @error('assign_advertiser_id') is-invalid @enderror">
                <option></option>
                @foreach ($advertisers as $advertiser)
                    <option value="{{ $advertiser['id'] }}">{{ $advertiser['name'] }}</option>
                @endforeach
            </select>
        </div>
        @error('assign_advertiser_id')
        <div class="invalid-feedback d-block mt-1" role="alert">
            <strong>{{ $message }}</strong>
        </div>
        @enderror
    </x-slot>
    <x-slot name="footer">
        <x-secondary-button wire:click="closeAssignAdvertiserModal" wire:loading.attr="disabled">
            {{ __('pages/subscriptions/packages/show.content.assign_advertiser_modal.cancel') }}
        </x-secondary-button>
        <x-primary-button type="submit" wire:loading.attr="disabled">
            {{ __('pages/subscriptions/packages/show.content.assign_advertiser_modal.submit') }}
        </x-primary-button>
    </x-slot>
</x-form-modal>

<x-confirmation-modal wire:model="showEndSubscriptionModal" type="delete">
    <x-slot name="title">
        {{ __('pages/subscriptions/packages/show.content.end_subscription_modal.title') }}
    </x-slot>
    <x-slot name="content">
        {{ __('pages/subscriptions/packages/show.content.end_subscription_modal.content', ['name' => $end_subscription_advertiser_name ?? '']) }}
    </x-slot>
    <x-slot name="footer">
        <x-secondary-button wire:click="closeEndSubscriptionModal" wire:loading.attr="disabled">
            {{ __('pages/subscriptions/packages/show.content.end_subscription_modal.cancel') }}
        </x-secondary-button>
        <x-danger-button wire:loading.attr="disabled" wire:click="endSubscription">
            {{ __('pages/subscriptions/packages/show.content.end_subscription_modal.submit') }}
        </x-danger-button>
    </x-slot>
</x-confirmation-modal>
</div>
