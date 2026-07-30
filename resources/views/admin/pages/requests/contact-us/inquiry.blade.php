@php
    $isUnread = $contact->status === 'unread';
    $typeLabel = __("pages/requests/contact-us/inquiry.content.types.{$contact->type}");
    $statusLabel = __("pages/requests/contact-us/inquiry.content.status_labels.{$contact->status}");
@endphp

<div class="contact-show" wire:key="contact-show-{{ $contact_id }}">
    <style>
        .contact-show {
            --cs-accent: #1565c0;
            --cs-accent-soft: rgba(21, 101, 192, 0.08);
            --cs-success: #2e7d32;
            --cs-warning: #ef6c00;
            --cs-danger: #c62828;
            --cs-muted: #607d8b;
            --cs-border: #e3e8ef;
            --cs-card: #fff;
            --cs-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .contact-show__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .contact-show__toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .contact-show__hero {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.16), transparent 42%),
                linear-gradient(135deg, #1e88e5 0%, #1565c0 55%, #0d47a1 100%);
            box-shadow: var(--cs-shadow);
        }

        .contact-show__hero.is-read {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.12), transparent 42%),
                linear-gradient(135deg, #546e7a 0%, #455a64 55%, #37474f 100%);
        }

        .contact-show__hero::after {
            content: "";
            position: absolute;
            inset: auto -2rem -3rem auto;
            width: 11rem;
            height: 11rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .contact-show__hero-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .contact-show__eyebrow {
            margin: 0 0 .4rem;
            opacity: .85;
            font-size: .85rem;
            letter-spacing: .02em;
        }

        .contact-show__hero h3 {
            margin: 0 0 .35rem;
            font-size: 1.55rem;
            font-weight: 700;
        }

        .contact-show__hero-meta {
            margin: 0;
            opacity: .9;
            font-size: .95rem;
        }

        .contact-show__badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .contact-show__badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .contact-show__badge--status-unread {
            background: rgba(255, 193, 7, 0.28);
            border-color: rgba(255, 236, 179, 0.45);
        }

        .contact-show__badge--status-read {
            background: rgba(129, 199, 132, 0.28);
            border-color: rgba(200, 230, 201, 0.45);
        }

        .contact-show__id-block {
            text-align: end;
            min-width: 7rem;
            position: relative;
            z-index: 1;
        }

        .contact-show__id-label {
            opacity: .8;
            font-size: .8rem;
        }

        .contact-show__id-value {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .contact-show__grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .contact-show__grid {
                grid-template-columns: 1fr;
            }
        }

        .contact-show__panel {
            background: var(--cs-card);
            border: 1px solid var(--cs-border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            box-shadow: var(--cs-shadow);
        }

        .contact-show__panel-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0 0 1rem;
            font-size: 1rem;
            font-weight: 700;
            color: #263238;
        }

        .contact-show__panel-title i {
            color: var(--cs-accent);
        }

        .contact-show__fields {
            display: grid;
            gap: .85rem;
        }

        .contact-show__field {
            display: grid;
            gap: .2rem;
            padding: .85rem 1rem;
            border-radius: .75rem;
            background: #f7f9fc;
            border: 1px solid #eef2f7;
        }

        .contact-show__field-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            color: var(--cs-muted);
            font-size: .8rem;
            font-weight: 600;
        }

        .contact-show__field-value {
            color: #263238;
            font-size: 1rem;
            font-weight: 700;
            word-break: break-word;
        }

        .contact-show__field-value a {
            color: var(--cs-accent);
            font-weight: 700;
        }

        .contact-show__quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1rem;
        }

        .contact-show__message {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.8;
            color: #37474f;
            font-size: 1.02rem;
            background: linear-gradient(180deg, #f8fbff 0%, #fff 100%);
            border: 1px dashed #cfd8dc;
            border-radius: .85rem;
            padding: 1.1rem 1.2rem;
            min-height: 10rem;
        }
    </style>

    <div class="contact-show__toolbar">
        <button type="button" class="btn btn-secondary"
                wire:click="$emitUp('setContactId', null)">
            <i class="icon-arrow-right13 mr-1"></i>
            {{ __('pages/requests/contact-us/inquiry.content.back') }}
        </button>

        <div class="contact-show__toolbar-actions">
            <button type="button"
                    class="btn {{ $isUnread ? 'btn-warning' : 'btn-success' }}"
                    @cannot('requests.contact.us') disabled @endcannot
                    wire:click="showConfirmModal"
                    title="{{ $isUnread
                        ? __('pages/requests/contact-us/inquiry.content.actions.mark_read')
                        : __('pages/requests/contact-us/inquiry.content.actions.mark_unread') }}">
                <i class="{{ $isUnread ? 'icon-eye' : 'icon-eye-blocked' }} mr-1"></i>
                {{ $isUnread
                    ? __('pages/requests/contact-us/inquiry.content.actions.mark_read')
                    : __('pages/requests/contact-us/inquiry.content.actions.mark_unread') }}
            </button>

            <button type="button"
                    class="btn btn-danger"
                    @cannot('requests.contact.us') disabled @endcannot
                    wire:click="showDeleteModal"
                    title="{{ __('pages/requests/contact-us/inquiry.content.actions.delete') }}">
                <i class="icon-trash mr-1"></i>
                {{ __('pages/requests/contact-us/inquiry.content.actions.delete') }}
            </button>
        </div>
    </div>

    <div class="contact-show__hero{{ $isUnread ? '' : ' is-read' }}">
        <div class="contact-show__hero-top">
            <div>
                <p class="contact-show__eyebrow">
                    {!! __('pages/requests/contact-us/inquiry.content.title', ['id' => $contact->id]) !!}
                </p>
                <h3>{{ $contact->name }}</h3>
                <p class="contact-show__hero-meta">
                    <i class="icon-calendar22 mr-1"></i>
                    {{ \Carbon\Carbon::make($contact->created_at)->format('Y-m-d h:i A') }}
                    <span class="mx-1">·</span>
                    {{ \Carbon\Carbon::make($contact->created_at)->diffForHumans() }}
                </p>
            </div>
            <div class="contact-show__id-block">
                <div class="contact-show__id-label">#</div>
                <div class="contact-show__id-value">{{ $contact->id }}</div>
            </div>
        </div>

        <div class="contact-show__badges">
            <span class="contact-show__badge">
                <i class="icon-folder"></i>
                {{ $typeLabel }}
            </span>
            <span class="contact-show__badge contact-show__badge--status-{{ $contact->status }}">
                <i class="{{ $isUnread ? 'icon-notification2' : 'icon-checkmark3' }}"></i>
                {{ $statusLabel }}
            </span>
        </div>
    </div>

    <div class="contact-show__grid">
        <div class="contact-show__panel">
            <h5 class="contact-show__panel-title">
                <i class="icon-user"></i>
                {{ __('pages/requests/contact-us/inquiry.content.sections.contact_info') }}
            </h5>

            <div class="contact-show__fields">
                <div class="contact-show__field">
                    <div class="contact-show__field-label">
                        <i class="icon-phone2"></i>
                        {{ __('pages/requests/contact-us/inquiry.content.mobile') }}
                    </div>
                    <div class="contact-show__field-value" dir="ltr">
                        <a href="tel:{{ $contact->mobile }}">{{ $contact->mobile }}</a>
                    </div>
                </div>

                <div class="contact-show__field">
                    <div class="contact-show__field-label">
                        <i class="icon-bubbles4"></i>
                        {{ __('pages/requests/contact-us/inquiry.content.whatsapp_mobile') }}
                    </div>
                    <div class="contact-show__field-value" dir="ltr">
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contact->whatsappMobile) }}"
                           target="_blank" rel="noopener noreferrer">
                            {{ $contact->whatsappMobile }}
                        </a>
                    </div>
                </div>

                <div class="contact-show__field">
                    <div class="contact-show__field-label">
                        <i class="icon-envelop3"></i>
                        {{ __('pages/requests/contact-us/inquiry.content.email') }}
                    </div>
                    <div class="contact-show__field-value">
                        @if($contact->email)
                            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

            <div class="contact-show__quick-links">
                <a class="btn btn-outline-primary btn-sm" href="tel:{{ $contact->mobile }}">
                    <i class="icon-phone2 mr-1"></i>
                    {{ __('pages/requests/contact-us/inquiry.content.actions.call') }}
                </a>
                <a class="btn btn-outline-success btn-sm"
                   href="https://wa.me/{{ preg_replace('/\D+/', '', $contact->whatsappMobile) }}"
                   target="_blank" rel="noopener noreferrer">
                    <i class="icon-bubbles4 mr-1"></i>
                    {{ __('pages/requests/contact-us/inquiry.content.actions.whatsapp') }}
                </a>
                @if($contact->email)
                    <a class="btn btn-outline-secondary btn-sm" href="mailto:{{ $contact->email }}">
                        <i class="icon-envelop3 mr-1"></i>
                        {{ __('pages/requests/contact-us/inquiry.content.actions.email') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="contact-show__panel">
            <h5 class="contact-show__panel-title">
                <i class="icon-comment-discussion"></i>
                {{ __('pages/requests/contact-us/inquiry.content.sections.message') }}
            </h5>
            <div class="contact-show__message">
                {!! nl2br(e($contact->message)) !!}
            </div>
        </div>
    </div>

    @include('modals.requests.contact-us.read')

    <x-confirmation-modal wire:model="showDeleteModal" type="delete">
        <x-slot name="title">
            {{ $deleteModalTexts['title'] ?? null }}
        </x-slot>
        <x-slot name="content">
            {{ $deleteModalTexts['content'] ?? null }}
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="closeDeleteModal" wire:loading.attr="disabled">
                {{ $deleteModalTexts['cancel'] ?? null }}
            </x-secondary-button>
            <x-danger-button wire:loading.attr="disabled" wire:click="deleteContact">
                {{ $deleteModalTexts['submit'] ?? null }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
