@php
    $isDeleted = !empty($offer->deleted_at);
    $isPending = $offer->status === 'pending';
    $isApproved = $offer->status === 'approved';
    $statusLabel = $isApproved
        ? __('pages/community/offers/show.modal.edit.inputs.approved')
        : __('pages/community/offers/show.modal.edit.inputs.pending');
    $expiresAt = $offer->expires_at
        ? \Carbon\Carbon::make($offer->expires_at)->format('Y-m-d h:i A')
        : '—';
    $advertiserName = optional($offer->advertiser)->name ?? '—';
    $advertiserId = optional($offer->advertiser)->id ?? '—';
@endphp

<div class="offer-show" wire:init="loadScripts" wire:key="offer-show-{{ $offer_id }}">
    <style>
        .offer-show {
            --os-accent: #00897b;
            --os-blue: #1565c0;
            --os-success: #2e7d32;
            --os-warning: #ef6c00;
            --os-danger: #c62828;
            --os-muted: #607d8b;
            --os-border: #e3e8ef;
            --os-card: #fff;
            --os-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .offer-show__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .offer-show__toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .offer-show__hero {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.16), transparent 42%),
                linear-gradient(135deg, #26a69a 0%, #00897b 55%, #00695c 100%);
            box-shadow: var(--os-shadow);
        }

        .offer-show__hero.is-pending {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.14), transparent 42%),
                linear-gradient(135deg, #ffa726 0%, #fb8c00 55%, #ef6c00 100%);
        }

        .offer-show__hero.is-deleted {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.12), transparent 42%),
                linear-gradient(135deg, #90a4ae 0%, #607d8b 55%, #455a64 100%);
        }

        .offer-show__hero::after {
            content: "";
            position: absolute;
            inset: auto -2rem -3rem auto;
            width: 11rem;
            height: 11rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .offer-show__hero-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .offer-show__eyebrow {
            margin: 0 0 .4rem;
            opacity: .88;
            font-size: .85rem;
        }

        .offer-show__hero h3 {
            margin: 0 0 .35rem;
            font-size: 1.45rem;
            font-weight: 700;
            max-width: 42rem;
            line-height: 1.35;
        }

        .offer-show__hero-meta {
            margin: 0;
            opacity: .92;
            font-size: .92rem;
        }

        .offer-show__badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .offer-show__badge {
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

        .offer-show__sale {
            text-align: end;
            min-width: 8rem;
            position: relative;
            z-index: 1;
        }

        .offer-show__sale-label {
            opacity: .85;
            font-size: .8rem;
        }

        .offer-show__sale-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .offer-show__metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .offer-show__metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .offer-show__metric {
            background: var(--os-card);
            border: 1px solid var(--os-border);
            border-radius: .9rem;
            padding: 1rem 1.1rem;
            box-shadow: var(--os-shadow);
        }

        .offer-show__metric-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            color: var(--os-muted);
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        .offer-show__metric-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #263238;
        }

        .offer-show__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .offer-show__grid {
                grid-template-columns: 1fr;
            }
        }

        .offer-show__panel {
            background: var(--os-card);
            border: 1px solid var(--os-border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            box-shadow: var(--os-shadow);
            margin-bottom: 1.25rem;
        }

        .offer-show__panel-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0 0 1rem;
            font-size: 1rem;
            font-weight: 700;
            color: #263238;
        }

        .offer-show__panel-title i {
            color: var(--os-accent);
        }

        .offer-show__fields {
            display: grid;
            gap: .75rem;
        }

        .offer-show__field {
            display: grid;
            gap: .2rem;
            padding: .8rem 1rem;
            border-radius: .75rem;
            background: #f7f9fc;
            border: 1px solid #eef2f7;
        }

        .offer-show__field-label {
            color: var(--os-muted);
            font-size: .78rem;
            font-weight: 600;
        }

        .offer-show__field-value {
            color: #263238;
            font-size: .98rem;
            font-weight: 700;
            word-break: break-word;
        }

        .offer-show__field-value a {
            color: var(--os-blue);
        }

        .offer-show__content {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.8;
            color: #37474f;
            font-size: 1.02rem;
            background: linear-gradient(180deg, #f1faf8 0%, #fff 100%);
            border: 1px dashed #b2dfdb;
            border-radius: .85rem;
            padding: 1.1rem 1.2rem;
        }

        .offer-show__media {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .offer-show__media li {
            width: 150px;
            position: relative;
        }

        .offer-show__media img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: .75rem;
            border: 1px solid var(--os-border);
        }

        .offer-show__media .delete-image {
            position: absolute;
            top: .35rem;
            inset-inline-end: .35rem;
            z-index: 2;
            background: rgba(255, 255, 255, .92);
            border-radius: 999px;
            width: 1.7rem;
            height: 1.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .offer-show__media-caption {
            margin-top: .35rem;
            text-align: center;
            font-size: .8rem;
            font-weight: 600;
            color: var(--os-muted);
        }
    </style>

    <div class="offer-show__toolbar">
        <button type="button" class="btn btn-secondary"
                wire:click="$emitUp('setOfferId', null)">
            <i class="icon-arrow-right13 mr-1"></i>
            {{ __('pages/community/offers/show.content.back') }}
        </button>

        <div class="offer-show__toolbar-actions">
            <button type="button"
                    class="btn btn-primary"
                    @cannot('offers.edit') disabled @endcannot
                    wire:click="showEditModal({{ $offer_id }})">
                <i class="icon-pencil7 mr-1"></i>
                {{ __('pages/community/offers/show.content.edit') }}
            </button>

            @unless($isDeleted)
                <button type="button"
                        class="btn btn-danger"
                        @cannot('offers.delete') disabled @endcannot
                        wire:click="showOfferDeleteModal">
                    <i class="icon-trash mr-1"></i>
                    {{ __('pages/community/offers/show.content.delete_offer') }}
                </button>
            @endunless
        </div>
    </div>

    <div class="offer-show__hero{{ $isDeleted ? ' is-deleted' : ($isPending ? ' is-pending' : '') }}">
        <div class="offer-show__hero-top">
            <div>
                <p class="offer-show__eyebrow">
                    {!! __('pages/community/offers/show.content.title', ['id' => $offer->id]) !!}
                </p>
                <h3>{{ \Illuminate\Support\Str::limit(strip_tags($offer->content), 90) }}</h3>
                <p class="offer-show__hero-meta">
                    <i class="icon-store2 mr-1"></i>{{ $advertiserName }}
                    @if(!empty($offer['category_name']))
                        <span class="mx-1">·</span>
                        <i class="icon-folder mr-1"></i>{{ $offer['category_name'] }}
                    @endif
                </p>
            </div>
            <div class="offer-show__sale">
                <div class="offer-show__sale-label">{{ __('pages/community/offers/show.content.sale_percentage') }}</div>
                <div class="offer-show__sale-value">{{ $offer->sale_percentage ? number_format((float) $offer->sale_percentage, 0) . '%' : '—' }}</div>
            </div>
        </div>

        <div class="offer-show__badges">
            <span class="offer-show__badge">
                <i class="icon-checkmark-circle"></i>
                {{ $statusLabel }}
            </span>
            <span class="offer-show__badge">
                <i class="icon-star-full2"></i>
                {{ __('pages/community/offers/show.content.rate') }}: {{ $offer->rate ?? '—' }}
            </span>
            <span class="offer-show__badge">
                <i class="icon-calendar22"></i>
                {{ __('pages/community/offers/show.content.expires_at') }}: {{ $expiresAt }}
            </span>
            @if($offer->expires_in)
                <span class="offer-show__badge">
                    <i class="icon-watch2"></i>
                    {{ __('pages/community/offers/show.content.expires_in') }}: {{ $offer->expires_in }}
                </span>
            @endif
        </div>
    </div>

    <div class="offer-show__metrics">
        <div class="offer-show__metric">
            <div class="offer-show__metric-label">
                <i class="icon-eye"></i>
                {{ __('pages/community/offers/show.content.views_count') }}
            </div>
            <div class="offer-show__metric-value">{{ $offer->views_count ?? 0 }}</div>
        </div>
        <div class="offer-show__metric">
            <div class="offer-show__metric-label">
                <i class="icon-heart5"></i>
                {{ __('pages/community/offers/show.content.likes_count') }}
            </div>
            <div class="offer-show__metric-value">{{ $offer->likes_count ?? 0 }}</div>
        </div>
        <div class="offer-show__metric">
            <div class="offer-show__metric-label">
                <i class="icon-comment"></i>
                {{ __('pages/community/offers/show.content.comments_count') }}
            </div>
            <div class="offer-show__metric-value">{{ $offer->comments_count ?? 0 }}</div>
        </div>
        <div class="offer-show__metric">
            <div class="offer-show__metric-label">
                <i class="icon-star-full2"></i>
                {{ __('pages/community/offers/show.content.rate') }}
            </div>
            <div class="offer-show__metric-value">{{ $offer->rate ?? '—' }}</div>
        </div>
    </div>

    <div class="offer-show__grid">
        <div class="offer-show__panel mb-0">
            <h5 class="offer-show__panel-title">
                <i class="icon-user"></i>
                {{ __('pages/community/offers/show.content.sections.advertiser') }}
            </h5>
            <div class="offer-show__fields">
                <div class="offer-show__field">
                    <div class="offer-show__field-label">{{ __('pages/community/offers/show.content.user_name') }}</div>
                    <div class="offer-show__field-value">{{ $advertiserName }}</div>
                </div>
                <div class="offer-show__field">
                    <div class="offer-show__field-label">{{ __('pages/community/offers/show.content.user_id') }}</div>
                    <div class="offer-show__field-value">{{ $advertiserId }}</div>
                </div>
                <div class="offer-show__field">
                    <div class="offer-show__field-label">{{ __('pages/community/offers/show.content.category') }}</div>
                    <div class="offer-show__field-value">{{ $offer['category_name'] ?? '—' }}</div>
                </div>
                <div class="offer-show__field">
                    <div class="offer-show__field-label">{{ __('pages/community/offers/show.content.advertisement_url') }}</div>
                    <div class="offer-show__field-value">
                        @if($offer->advertisement_url)
                            <a href="{{ $offer->advertisement_url }}" target="_blank" rel="noopener noreferrer">
                                {{ $offer->advertisement_url }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="offer-show__panel mb-0">
            <h5 class="offer-show__panel-title">
                <i class="icon-file-text2"></i>
                {{ __('pages/community/offers/show.content.sections.content') }}
            </h5>
            <div class="offer-show__content">
                {!! nl2br(e(strip_tags($offer->content))) !!}
            </div>
            @if($isDeleted)
                <div class="offer-show__field mt-3">
                    <div class="offer-show__field-label">{{ __('pages/community/offers/show.content.deleted_at') }}</div>
                    <div class="offer-show__field-value">{{ $offer->deleted_at }}</div>
                </div>
            @endif
        </div>
    </div>

    @if($offer->getMedia('offers')->count() > 0)
        <div class="offer-show__panel">
            <h5 class="offer-show__panel-title">
                <i class="icon-images3"></i>
                {{ __('pages/community/offers/show.content.sections.media') }}
            </h5>
            <ul id="animated-thumbnails-gallery" dir="ltr" class="offer-show__media">
                @foreach ($offer->getMedia('offers') as $index => $media)
                    @php($media_url = \App\Helpers\Files::mediaUrl($media))
                    <li data-id="{{ $media->id }}" data-src="{{ $media_url }}">
                        <a wire:click="showDeleteModal({{ $media->id }})" class="delete-image text-danger" href="javascript:void(0)">
                            <i class="icon-trash"></i>
                        </a>
                        <img data-id="{{ $media->id }}"
                             data-src="{{ $media_url }}"
                             data-download-url="{{ route('media.download', $media->uuid) }}"
                             data-thumb="{{ $media_url }}"
                             class="grid-square"
                             alt="{{ $media->name }}"
                             src="{{ $media_url }}"/>
                        <div class="offer-show__media-caption">
                            {{ __('pages/community/offers/show.content.media') }} {{ $index + 1 }}
                        </div>
                    </li>
                @endforeach
            </ul>
            <button style="display: none" id="save_order" class="btn btn-secondary mt-3"
                    wire:click="setOrder(localStorage.getItem('order').split('|'));">
                {{ __('pages/community/offers/show.content.save') }}
            </button>
        </div>
    @endif

    @if($offer->getMedia('offers')->count() > 0)
        <script src="{{ asset('assets/plugins/light-gallery/plugins/lg-delete.js') }}"></script>
        <script type="text/javascript">
            const $lgDemoUpdateSlides = document.getElementById('animated-thumbnails-gallery');
            let updateSlideInstance;
            window.addEventListener('loadScripts', () => {
                localStorage.removeItem('order');

                let sortable = new Sortable($lgDemoUpdateSlides, {
                    group: { name: 'images_order', pull: true },
                    swapThreshold: 1,
                    animation: 150,
                    store: {
                        get: function (sortable) {
                            localStorage.setItem('order', {!! json_encode($order) !!}.join('|'));
                            return {!! json_encode($order) !!};
                        },
                        set: function (sortable) {
                            let order = sortable.toArray();
                            $('#save_order').show();
                            localStorage.setItem('order', order.join('|'));
                        }
                    }
                });

                updateSlideInstance = lightGallery($lgDemoUpdateSlides, {
                    plugins: [lgZoom, lgRotate, lgVideo],
                    thumbnail: true,
                    animateThumb: true,
                    zoomFromOrigin: true,
                    allowMediaOverlap: true,
                    toggleThumb: true,
                    selector: '.grid-square'
                });
            });

            window.addEventListener('clearFileInput', (event) => {
                $('#media').val(null);
                let order = event.detail;
                let sortable = new Sortable($lgDemoUpdateSlides, {
                    group: { name: 'images_order', pull: true },
                    swapThreshold: 1,
                    animation: 150,
                    store: {
                        get: function (sortable) {
                            localStorage.setItem('order', order.join('|'));
                            return order;
                        },
                        set: function (sortable) {
                            let order = sortable.toArray();
                            $('#save_order').show();
                            localStorage.setItem('order', order.join('|'));
                        }
                    }
                });
            });

            window.addEventListener('resetLightGallery', (event) => {
                let $container = document.querySelector('.lg-container');
                if ($container) {
                    $container.remove();
                }
                if (localStorage.getItem('order') && localStorage.getItem('order').split('|').length > 0) {
                    updateSlideInstance = lightGallery($lgDemoUpdateSlides, {
                        plugins: [lgZoom, lgRotate, lgVideo],
                        thumbnail: true,
                        animateThumb: true,
                        zoomFromOrigin: true,
                        allowMediaOverlap: true,
                        toggleThumb: true,
                        selector: '.grid-square',
                        galleryId: "gallery2",
                    });
                }
            });
        </script>
    @endif

    @include('modals.community.offers.editMedia')
    @include('modals.community.offers.delete')
    @include('modals.community.offers.delete-offer')
</div>
