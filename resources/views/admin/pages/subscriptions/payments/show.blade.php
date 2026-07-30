<div class="payment-show" wire:key="payment-show-{{ $payment_id }}">
    <style>
        .payment-show {
            --ps-accent: #00897b;
            --ps-accent-soft: rgba(0, 137, 123, 0.1);
            --ps-blue: #1e88e5;
            --ps-success: #43a047;
            --ps-danger: #e53935;
            --ps-warning: #fb8c00;
            --ps-muted: #78909c;
            --ps-border: #e3e8ef;
            --ps-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .payment-show__toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.25rem;
        }

        .payment-show__hero {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.16), transparent 42%),
                linear-gradient(135deg, #00897b 0%, #00695c 55%, #004d40 100%);
            box-shadow: var(--ps-shadow);
        }

        .payment-show__hero.is-ended {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.12), transparent 42%),
                linear-gradient(135deg, #78909c 0%, #546e7a 55%, #37474f 100%);
        }

        .payment-show__hero.is-deleted {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.12), transparent 42%),
                linear-gradient(135deg, #ef5350 0%, #c62828 60%, #b71c1c 100%);
        }

        .payment-show__hero-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .payment-show__names h3 {
            margin: 0 0 .35rem;
            font-size: 1.55rem;
            font-weight: 700;
        }

        .payment-show__names .secondary-name {
            margin: 0;
            opacity: .88;
            font-size: 1.05rem;
        }

        .payment-show__meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .85rem;
            margin-inline-end: .45rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: .82rem;
        }

        .payment-show__id-block {
            text-align: end;
            min-width: 8rem;
        }

        .payment-show__id-label {
            opacity: .8;
            font-size: .8rem;
        }

        .payment-show__id-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .payment-show__badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .payment-show__badge {
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

        .payment-show__badge:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.26);
        }

        .payment-show__badge.is-off {
            opacity: .55;
        }

        .payment-show__metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .85rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 767px) {
            .payment-show__metrics {
                grid-template-columns: 1fr;
            }

            .payment-show__id-block {
                text-align: start;
            }
        }

        .payment-show__metric {
            background: #fff;
            border: 1px solid var(--ps-border);
            border-radius: .9rem;
            padding: 1rem 1.1rem;
            box-shadow: var(--ps-shadow);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .payment-show__metric:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 137, 123, 0.35);
            box-shadow: 0 14px 28px rgba(0, 137, 123, 0.12);
        }

        .payment-show__metric-label {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: var(--ps-muted);
            font-size: .82rem;
            margin-bottom: .45rem;
        }

        .payment-show__metric-label i {
            color: var(--ps-accent);
        }

        .payment-show__metric-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #004d40;
            line-height: 1.35;
        }

        .payment-show__section {
            background: #fff;
            border: 1px solid var(--ps-border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            margin-bottom: 1.25rem;
            box-shadow: var(--ps-shadow);
        }

        .payment-show__section-title {
            margin: 0 0 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #263238;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .payment-show__section-title::before {
            content: "";
            width: .35rem;
            height: 1.1rem;
            border-radius: 999px;
            background: var(--ps-accent);
        }

        .payment-show__timeline {
            position: relative;
            padding: .5rem 0 0;
        }

        .payment-show__timeline-track {
            height: .55rem;
            border-radius: 999px;
            background: #eceff1;
            overflow: hidden;
            margin-bottom: .85rem;
        }

        .payment-show__timeline-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #26a69a, #00897b);
            transition: width .4s ease;
        }

        .payment-show__timeline-fill.is-ended {
            background: linear-gradient(90deg, #90a4ae, #607d8b);
        }

        .payment-show__timeline-fill.is-deleted {
            background: linear-gradient(90deg, #ef9a9a, #e53935);
        }

        .payment-show__timeline-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: .75rem;
            color: #546e7a;
            font-size: .9rem;
        }

        .payment-show__timeline-meta strong {
            display: block;
            color: #263238;
            margin-top: .2rem;
        }

        .payment-show__cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 767px) {
            .payment-show__cards {
                grid-template-columns: 1fr;
            }
        }

        .payment-show__card {
            border: 1px solid var(--ps-border);
            border-radius: .85rem;
            padding: 1rem 1.1rem;
            background: linear-gradient(180deg, #fff 0%, #f7fffe 100%);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .payment-show__card:hover {
            transform: translateY(-3px);
            border-color: rgba(0, 137, 123, 0.35);
            box-shadow: 0 10px 22px rgba(0, 137, 123, 0.1);
        }

        .payment-show__card-label {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .75rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            background: var(--ps-accent-soft);
            color: var(--ps-accent);
            font-size: .75rem;
            font-weight: 700;
        }

        .payment-show__card h4 {
            margin: 0 0 .35rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #263238;
        }

        .payment-show__card p {
            margin: 0;
            color: #607d8b;
            font-size: .9rem;
        }

        .payment-show__status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: .75rem;
        }

        .payment-show__status-item {
            padding: .85rem 1rem;
            border-radius: .85rem;
            border: 1px solid var(--ps-border);
            background: #fff;
            transition: transform .2s ease, border-color .2s ease;
        }

        .payment-show__status-item:hover {
            transform: translateY(-2px);
            border-color: rgba(0, 137, 123, 0.3);
        }

        .payment-show__status-item span {
            display: block;
            color: var(--ps-muted);
            font-size: .78rem;
            margin-bottom: .3rem;
        }

        .payment-show__status-item strong {
            color: #263238;
            font-size: .95rem;
        }

        .payment-show__pill {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
        }

        .payment-show__pill.yes {
            background: rgba(67, 160, 71, 0.12);
            color: var(--ps-success);
        }

        .payment-show__pill.no {
            background: rgba(120, 144, 156, 0.14);
            color: #607d8b;
        }

        .payment-show__pill.danger {
            background: rgba(229, 57, 53, 0.12);
            color: var(--ps-danger);
        }
    </style>

    @php
        $locale = app()->getLocale();
        $package = $paymentData->package;
        $advertiser = $paymentData->advertiser;
        $packageName = $package
            ? ($locale === 'ar' ? ($package->name_ar ?: $package->name_en) : ($package->name_en ?: $package->name_ar))
            : '-';
        $packageSecondary = $package
            ? ($locale === 'ar' ? $package->name_en : $package->name_ar)
            : null;
        $startsAt = $paymentData->starts_at ? \Carbon\Carbon::make($paymentData->starts_at) : null;
        $endsAt = $paymentData->ends_at ? \Carbon\Carbon::make($paymentData->ends_at) : null;
        $deletedAt = $paymentData->deleted_at ? \Carbon\Carbon::make($paymentData->deleted_at) : null;
        $progress = 0;
        $remainingLabel = __('pages/subscriptions/payments/show.content.no_end_date');
        if ($startsAt && $endsAt) {
            $total = max($startsAt->diffInSeconds($endsAt), 1);
            $elapsed = max(min($startsAt->diffInSeconds(now()), $total), 0);
            $progress = (int) round(($elapsed / $total) * 100);
            if ($endsAt->isPast()) {
                $remainingLabel = __('pages/subscriptions/payments/show.content.ended_ago', [
                    'days' => $endsAt->diffInDays(now()),
                ]);
                $progress = 100;
            } else {
                $remainingLabel = __('pages/subscriptions/payments/show.content.remaining_days', [
                    'days' => now()->diffInDays($endsAt),
                ]);
            }
        }
        $heroClass = $deletedAt ? 'is-deleted' : ($paymentData->is_ended ? 'is-ended' : '');
        $fillClass = $deletedAt ? 'is-deleted' : ($paymentData->is_ended ? 'is-ended' : '');
    @endphp

    <div class="payment-show__toolbar">
        <button class="btn btn-secondary" wire:click="$emitUp('setPaymentId', null)">
            <i class="icon-arrow-right13 mr-1"></i>
            {{ __('pages/subscriptions/payments/show.content.back') }}
        </button>
        <button @cannot('payments.edit') disabled @endcannot
                wire:click="showEditModal({{ $payment_id }})"
                class="btn btn-primary">
            <i class="icon-pencil7 mr-1"></i>
            {{ __('pages/subscriptions/payments/show.content.edit') }}
        </button>
        @if(!$deletedAt)
            <button @cannot('payments.delete') disabled @endcannot
                    wire:click="showDeleteModal"
                    class="btn btn-danger">
                <i class="icon-trash mr-1"></i>
                {{ __('pages/subscriptions/payments/show.content.delete') }}
            </button>
        @endif
    </div>

    <div class="payment-show__hero {{ $heroClass }}">
        <div class="payment-show__hero-top">
            <div class="payment-show__names">
                <h3>{{ $packageName }}</h3>
                @if($packageSecondary)
                    <p class="secondary-name">{{ $packageSecondary }}</p>
                @endif
                <div>
                    <span class="payment-show__meta-chip">
                        <i class="icon-user"></i>
                        {{ $advertiser->name ?? '-' }}
                    </span>
                    <span class="payment-show__meta-chip">
                        <i class="icon-file-text2"></i>
                        {{ __('pages/subscriptions/payments/show.content.payment_id') }}: {{ $paymentData->id }}
                    </span>
                </div>
            </div>
            <div class="payment-show__id-block">
                <div class="payment-show__id-label">{{ __('pages/subscriptions/payments/show.content.package_id') }}</div>
                <div class="payment-show__id-value">#{{ $paymentData->package_id }}</div>
            </div>
        </div>

        <div class="payment-show__badges">
            <span class="payment-show__badge {{ $paymentData->is_active ? '' : 'is-off' }}">
                <i class="{{ $paymentData->is_active ? 'icon-checkmark3' : 'icon-cross2' }}"></i>
                {{ __('pages/subscriptions/payments/show.content.is_active') }}:
                {{ $paymentData->is_active ? __('pages/subscriptions/payments/show.content.boolean.yes') : __('pages/subscriptions/payments/show.content.boolean.no') }}
            </span>
            <span class="payment-show__badge {{ $paymentData->is_current ? '' : 'is-off' }}">
                <i class="icon-star-full2"></i>
                {{ __('pages/subscriptions/payments/show.content.is_current') }}:
                {{ $paymentData->is_current ? __('pages/subscriptions/payments/show.content.boolean.yes') : __('pages/subscriptions/payments/show.content.boolean.no') }}
            </span>
            <span class="payment-show__badge {{ $paymentData->is_ended ? '' : 'is-off' }}">
                <i class="icon-watch2"></i>
                {{ __('pages/subscriptions/payments/show.content.is_ended') }}:
                {{ $paymentData->is_ended ? __('pages/subscriptions/payments/show.content.boolean.yes') : __('pages/subscriptions/payments/show.content.boolean.no') }}
            </span>
        </div>
    </div>

    <div class="payment-show__metrics">
        <div class="payment-show__metric">
            <div class="payment-show__metric-label">
                <i class="icon-calendar52"></i>
                {{ __('pages/subscriptions/payments/show.content.starts_at') }}
            </div>
            <div class="payment-show__metric-value">
                {{ $startsAt ? $startsAt->format('Y-m-d h:i A') : '-' }}
            </div>
        </div>
        <div class="payment-show__metric">
            <div class="payment-show__metric-label">
                <i class="icon-calendar2"></i>
                {{ __('pages/subscriptions/payments/show.content.ends_at') }}
            </div>
            <div class="payment-show__metric-value">
                {{ $endsAt ? $endsAt->format('Y-m-d h:i A') : '-' }}
            </div>
        </div>
        <div class="payment-show__metric">
            <div class="payment-show__metric-label">
                <i class="icon-hour-glass"></i>
                {{ __('pages/subscriptions/payments/show.content.remaining') }}
            </div>
            <div class="payment-show__metric-value">{{ $remainingLabel }}</div>
        </div>
    </div>

    <div class="payment-show__section">
        <h5 class="payment-show__section-title">
            {{ __('pages/subscriptions/payments/show.content.timeline_title') }}
        </h5>
        <div class="payment-show__timeline">
            <div class="payment-show__timeline-track">
                <div class="payment-show__timeline-fill {{ $fillClass }}" style="width: {{ $progress }}%"></div>
            </div>
            <div class="payment-show__timeline-meta">
                <div>
                    {{ __('pages/subscriptions/payments/show.content.starts_at') }}
                    <strong>{{ $startsAt ? $startsAt->format('Y-m-d') : '-' }}</strong>
                </div>
                <div>
                    {{ __('pages/subscriptions/payments/show.content.progress') }}
                    <strong>{{ $progress }}%</strong>
                </div>
                <div>
                    {{ __('pages/subscriptions/payments/show.content.ends_at') }}
                    <strong>{{ $endsAt ? $endsAt->format('Y-m-d') : '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="payment-show__section">
        <h5 class="payment-show__section-title">
            {{ __('pages/subscriptions/payments/show.content.parties_title') }}
        </h5>
        <div class="payment-show__cards">
            <div class="payment-show__card">
                <div class="payment-show__card-label">
                    <i class="icon-stack2"></i>
                    {{ __('pages/subscriptions/payments/show.content.package_name') }}
                </div>
                <h4>{{ $packageName }}</h4>
                <p>{{ __('pages/subscriptions/payments/show.content.package_id') }}: #{{ $paymentData->package_id }}</p>
                @if($packageSecondary)
                    <p>{{ $packageSecondary }}</p>
                @endif
            </div>
            <div class="payment-show__card">
                <div class="payment-show__card-label">
                    <i class="icon-user"></i>
                    {{ __('pages/subscriptions/payments/show.content.advertiser_name') }}
                </div>
                <h4>{{ $advertiser->name ?? '-' }}</h4>
                <p>{{ __('pages/subscriptions/payments/show.content.advertiser_id') }}: #{{ $paymentData->advertiser_id }}</p>
            </div>
        </div>
    </div>

    <div class="payment-show__section">
        <h5 class="payment-show__section-title">
            {{ __('pages/subscriptions/payments/show.content.status_title') }}
        </h5>
        <div class="payment-show__status-grid">
            <div class="payment-show__status-item">
                <span>{{ __('pages/subscriptions/payments/show.content.is_active') }}</span>
                <strong>
                    <span class="payment-show__pill {{ $paymentData->is_active ? 'yes' : 'no' }}">
                        {{ $paymentData->is_active ? __('pages/subscriptions/payments/show.content.boolean.yes') : __('pages/subscriptions/payments/show.content.boolean.no') }}
                    </span>
                </strong>
            </div>
            <div class="payment-show__status-item">
                <span>{{ __('pages/subscriptions/payments/show.content.is_current') }}</span>
                <strong>
                    <span class="payment-show__pill {{ $paymentData->is_current ? 'yes' : 'no' }}">
                        {{ $paymentData->is_current ? __('pages/subscriptions/payments/show.content.boolean.yes') : __('pages/subscriptions/payments/show.content.boolean.no') }}
                    </span>
                </strong>
            </div>
            <div class="payment-show__status-item">
                <span>{{ __('pages/subscriptions/payments/show.content.is_ended') }}</span>
                <strong>
                    <span class="payment-show__pill {{ $paymentData->is_ended ? 'danger' : 'no' }}">
                        {{ $paymentData->is_ended ? __('pages/subscriptions/payments/show.content.boolean.yes') : __('pages/subscriptions/payments/show.content.boolean.no') }}
                    </span>
                </strong>
            </div>
            <div class="payment-show__status-item">
                <span>{{ __('pages/subscriptions/payments/show.content.deleted_at') }}</span>
                <strong>{{ $deletedAt ? $deletedAt->format('Y-m-d h:i A') : '-' }}</strong>
            </div>
            <div class="payment-show__status-item">
                <span>{{ __('pages/subscriptions/payments/show.content.purchase_count') }}</span>
                <strong>{{ $paymentData->purchase_count ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <x-confirmation-modal wire:model="showDeleteModal" type="delete">
        <x-slot name="title">
            {{ __('pages/subscriptions/payments/show.modal.delete.title') }}
        </x-slot>
        <x-slot name="content">
            {{ __('pages/subscriptions/payments/show.modal.delete.content', ['name' => $packageName]) }}
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="closeDeleteModal" wire:loading.attr="disabled">
                {{ __('pages/subscriptions/payments/show.modal.delete.cancel') }}
            </x-secondary-button>
            <x-danger-button wire:loading.attr="disabled" wire:click="deletePayment">
                {{ __('pages/subscriptions/payments/show.modal.delete.submit') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    @include('modals.subscriptions.payments.edit')
</div>
