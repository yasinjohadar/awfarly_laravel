@php
    $isDeleted = !empty($post->deleted_at);
    $isPending = $post->status === 'pending';
    $isApproved = $post->status === 'approved';
    $statusLabel = $isDeleted
        ? __('pages/community/posts/inquiry.content.status_deleted')
        : ($isApproved
            ? __('pages/community/posts/inquiry.content.status_approved')
            : __('pages/community/posts/inquiry.content.status_pending'));
@endphp

<div class="post-show" wire:init="loadScripts" wire:key="post-show-{{ $post_id }}">
    <style>
        .post-show {
            --ps-accent: #2e86d6;
            --ps-success: #2e7d32;
            --ps-warning: #ef6c00;
            --ps-muted: #607d8b;
            --ps-border: #e3e8ef;
            --ps-card: #fff;
            --ps-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .post-show__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .post-show__toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .post-show__hero {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.16), transparent 42%),
                linear-gradient(135deg, #42a5f5 0%, #2e86d6 55%, #1d599f 100%);
            box-shadow: var(--ps-shadow);
        }

        .post-show__hero.is-pending {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.14), transparent 42%),
                linear-gradient(135deg, #ffa726 0%, #fb8c00 55%, #ef6c00 100%);
        }

        .post-show__hero.is-deleted {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.12), transparent 42%),
                linear-gradient(135deg, #90a4ae 0%, #607d8b 55%, #455a64 100%);
        }

        .post-show__hero::after {
            content: "";
            position: absolute;
            inset: auto -2rem -3rem auto;
            width: 11rem;
            height: 11rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .post-show__eyebrow {
            margin: 0 0 .4rem;
            opacity: .88;
            font-size: .85rem;
            position: relative;
            z-index: 1;
        }

        .post-show__hero h3 {
            margin: 0 0 .35rem;
            font-size: 1.4rem;
            font-weight: 700;
            max-width: 46rem;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .post-show__hero-meta {
            margin: 0;
            opacity: .92;
            font-size: .92rem;
            position: relative;
            z-index: 1;
        }

        .post-show__badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .post-show__badge {
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

        .post-show__metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .85rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .post-show__metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .post-show__metric {
            background: var(--ps-card);
            border: 1px solid var(--ps-border);
            border-radius: .9rem;
            padding: 1rem 1.1rem;
            box-shadow: var(--ps-shadow);
        }

        .post-show__metric-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            color: var(--ps-muted);
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        .post-show__metric-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #263238;
        }

        .post-show__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .post-show__grid {
                grid-template-columns: 1fr;
            }
        }

        .post-show__panel {
            background: var(--ps-card);
            border: 1px solid var(--ps-border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            box-shadow: var(--ps-shadow);
            margin-bottom: 1.25rem;
        }

        .post-show__panel-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0 0 1rem;
            font-size: 1rem;
            font-weight: 700;
            color: #263238;
        }

        .post-show__panel-title i {
            color: var(--ps-accent);
        }

        .post-show__fields {
            display: grid;
            gap: .75rem;
        }

        .post-show__field {
            display: grid;
            gap: .2rem;
            padding: .8rem 1rem;
            border-radius: .75rem;
            background: #f7f9fc;
            border: 1px solid #eef2f7;
        }

        .post-show__field-label {
            color: var(--ps-muted);
            font-size: .78rem;
            font-weight: 600;
        }

        .post-show__field-value {
            color: #263238;
            font-size: .98rem;
            font-weight: 700;
            word-break: break-word;
        }

        .post-show__content {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.8;
            color: #37474f;
            font-size: 1.02rem;
            background: linear-gradient(180deg, #eaf3fc 0%, #fff 100%);
            border: 1px dashed #a1cbf0;
            border-radius: .85rem;
            padding: 1.1rem 1.2rem;
        }

        .post-show__media {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .post-show__media li {
            width: 240px;
            position: relative;
        }

        .post-show__media img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 1rem;
            border: 1px solid var(--ps-border);
            box-shadow: var(--ps-shadow);
        }

        .post-show__media .delete-image {
            position: absolute;
            top: .5rem;
            inset-inline-end: .5rem;
            z-index: 2;
            background: rgba(255, 255, 255, .92);
            border-radius: 999px;
            width: 1.9rem;
            height: 1.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .post-show__media-caption {
            margin-top: .4rem;
            text-align: center;
            font-size: .8rem;
            font-weight: 600;
            color: var(--ps-muted);
        }
    </style>

    <div class="post-show__toolbar">
        <button type="button" class="btn btn-secondary"
                wire:click="$emitUp('setPostId', null)">
            <i class="icon-arrow-right13 mr-1"></i>
            {{ __('pages/community/posts/inquiry.content.back') }}
        </button>

        <div class="post-show__toolbar-actions">
            @if($isPending && !$isDeleted)
                <button type="button"
                        class="btn btn-success"
                        @cannot('posts.edit') disabled @endcannot
                        wire:click="approve({{ $post->id }})"
                        wire:loading.attr="disabled"
                        wire:target="approve({{ $post->id }})"
                        onclick="return confirm('{{ __('pages/community/posts/inquiry.content.approve') }}?')">
                    <i class="icon-checkmark3 mr-1"></i>
                    {{ __('pages/community/posts/inquiry.content.approve') }}
                </button>
            @endif
            <button type="button"
                    class="btn btn-primary"
                    @cannot('posts.edit') disabled @endcannot
                    wire:click="showEditModal({{ $post_id }})">
                <i class="icon-pencil7 mr-1"></i>
                {{ __('pages/community/posts/inquiry.content.edit') }}
            </button>
        </div>
    </div>

    <div class="post-show__hero{{ $isDeleted ? ' is-deleted' : ($isPending ? ' is-pending' : '') }}">
        <p class="post-show__eyebrow">
            {!! __('pages/community/posts/inquiry.content.title', ['id' => $post->id]) !!}
        </p>
        <h3>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 110) }}</h3>
        <p class="post-show__hero-meta">
            <i class="icon-user mr-1"></i>{{ $post->user->name }}
            @if($post->category_name && $post->category_name !== '-')
                <span class="mx-1">·</span>
                <i class="icon-folder mr-1"></i>{{ $post->category_name }}
            @endif
        </p>

        <div class="post-show__badges">
            <span class="post-show__badge">
                <i class="icon-checkmark-circle"></i>
                {{ $statusLabel }}
            </span>
            <span class="post-show__badge">
                <i class="icon-user"></i>
                {{ ucwords($post->user->user_type) }}
            </span>
            @if($isDeleted)
                <span class="post-show__badge">
                    <i class="icon-calendar22"></i>
                    {{ __('pages/community/posts/inquiry.content.deleted_at') }}: {{ $post->deleted_at }}
                </span>
            @endif
        </div>
    </div>

    <div class="post-show__metrics">
        <div class="post-show__metric">
            <div class="post-show__metric-label">
                <i class="icon-eye"></i>
                {{ __('pages/community/posts/inquiry.content.views_count') }}
            </div>
            <div class="post-show__metric-value">{{ $post->views_count ?? 0 }}</div>
        </div>
        <div class="post-show__metric">
            <div class="post-show__metric-label">
                <i class="icon-heart5"></i>
                {{ __('pages/community/posts/inquiry.content.likes_count') }}
            </div>
            <div class="post-show__metric-value">{{ $post->likes_count ?? 0 }}</div>
        </div>
        <div class="post-show__metric">
            <div class="post-show__metric-label">
                <i class="icon-comment"></i>
                {{ __('pages/community/posts/inquiry.content.comments_count') }}
            </div>
            <div class="post-show__metric-value">{{ $post->comments_count ?? 0 }}</div>
        </div>
    </div>

    <div class="post-show__grid">
        <div class="post-show__panel mb-0">
            <h5 class="post-show__panel-title">
                <i class="icon-user"></i>
                {{ __('pages/community/posts/inquiry.content.sections.info') }}
            </h5>
            <div class="post-show__fields">
                <div class="post-show__field">
                    <div class="post-show__field-label">{{ __('pages/community/posts/inquiry.content.user_name') }}</div>
                    <div class="post-show__field-value">{{ $post->user->name }}</div>
                </div>
                <div class="post-show__field">
                    <div class="post-show__field-label">{{ __('pages/community/posts/inquiry.content.user_id') }}</div>
                    <div class="post-show__field-value">{{ $post->user->id }}</div>
                </div>
                <div class="post-show__field">
                    <div class="post-show__field-label">{{ __('pages/community/posts/inquiry.content.user_type') }}</div>
                    <div class="post-show__field-value">{{ ucwords($post->user->user_type) }}</div>
                </div>
                <div class="post-show__field">
                    <div class="post-show__field-label">{{ __('pages/community/posts/inquiry.content.category') }}</div>
                    <div class="post-show__field-value">{{ $post->category_name }}</div>
                </div>
            </div>
        </div>

        <div class="post-show__panel mb-0">
            <h5 class="post-show__panel-title">
                <i class="icon-file-text2"></i>
                {{ __('pages/community/posts/inquiry.content.sections.content') }}
            </h5>
            <div class="post-show__content">
                {!! nl2br(e(strip_tags($post->content))) !!}
            </div>
        </div>
    </div>

    @if($post->getMedia('posts')->count() > 0)
        <div class="post-show__panel">
            <h5 class="post-show__panel-title">
                <i class="icon-images3"></i>
                {{ __('pages/community/posts/inquiry.content.sections.media') }}
            </h5>
            <ul id="animated-thumbnails-gallery" dir="ltr" class="post-show__media">
                @foreach ($post->getMedia('posts') as $index => $media)
                    @php($media_url = \App\Helpers\Files::mediaUrl($media))
                    <li data-id="{{$media->id}}" data-src="{{$media_url}}">
                        <a wire:click="showDeleteModal({{$media->id}})" class="delete-image text-danger" href="javascript:void(0)">
                            <i class="icon-delete-o"></i>
                        </a>
                        <img data-id="{{$media->id}}" data-src="{{$media_url}}"
                             data-download-url="{{route('media.download', $media->uuid)}}"
                             data-thumb="{{$media_url}}"
                             class="grid-square" alt="{{$media->name}}" src="{{$media_url}}"/>
                        <div class="post-show__media-caption">
                            {{__('pages/community/posts/inquiry.content.image')}}{{$index+1}}
                        </div>
                    </li>
                @endforeach
            </ul>
            <button style="display: none" id="save_order" class="btn btn-secondary mt-3"
                    wire:click="setOrder(localStorage.getItem('order').split('|'));">
                {{__('pages/community/posts/inquiry.content.save')}}
            </button>
        </div>
    @endif

    @if($post->getMedia('posts')->count() > 0)
        <script src="{{ asset('assets/plugins/light-gallery/plugins/lg-delete.js') }}"></script>
        <script type="text/javascript">
            const $lgDemoUpdateSlides = document.getElementById(
                'animated-thumbnails-gallery',
            );
            let updateSlideInstance;
            window.addEventListener('loadScripts', () => {

                localStorage.removeItem('order');

                let sortable = new Sortable($lgDemoUpdateSlides, {
                    group: {
                        name: 'images_order',
                        pull: true,
                    },
                    swapThreshold: 1,
                    animation: 150,
                    store: {
                        /**
                         * Get the order of elements. Called once during initialization.
                         * @param   {Sortable}  sortable
                         * @returns {Array}
                         */
                        get: function (sortable) {
                            localStorage.setItem('order', {!! json_encode($order) !!}.join('|'));
                            return {!! json_encode($order) !!};
                        },


                        /**
                         * Save the order of elements. Called onEnd (when the item is dropped).
                         * @param {Sortable}  sortable
                         */
                        set: function (sortable) {
                            let order = sortable.toArray();
                            $('#save_order').show();
                            localStorage.setItem('order', order.join('|'));
                        }
                    }
                });

                updateSlideInstance = lightGallery($lgDemoUpdateSlides, {
                    plugins: [lgZoom, /*lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager,*/ lgRotate/*, lgShare*/, lgVideo],
                    thumbnail: true,
                    animateThumb: true,
                    zoomFromOrigin: true,
                    allowMediaOverlap: true,
                    toggleThumb: true,
                    selector: '.grid-square'
                });
            });
            //add event listener to refresh file input
            window.addEventListener('clearFileInput', (event) => {
                $('#media').val(null);
                let order = event.detail;
                let sortable = new Sortable($lgDemoUpdateSlides, {
                    group: {
                        name: 'images_order',
                        pull: true,
                    },
                    swapThreshold: 1,
                    animation: 150,
                    store: {
                        /**
                         * Get the order of elements. Called once during initialization.
                         * @param   {Sortable}  sortable
                         * @returns {Array}
                         */
                        get: function (sortable) {
                            localStorage.setItem('order', order.join('|'));
                            return order;
                        },


                        /**
                         * Save the order of elements. Called onEnd (when the item is dropped).
                         * @param {Sortable}  sortable
                         */
                        set: function (sortable) {
                            let order = sortable.toArray();
                            $('#save_order').show();
                            localStorage.setItem('order', order.join('|'));
                        }
                    }
                });
            });

            window.addEventListener('resetLightGallery', (event) => {
                /*updateSlideInstance.destroy()*/

                let $container = document.querySelector('.lg-container');
                if ($container) {
                    $container.remove();
                }
                if (localStorage.getItem('order').split('|').length > 0) {
                    updateSlideInstance = lightGallery($lgDemoUpdateSlides, {
                        plugins: [lgZoom, /*lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager,*/ lgRotate/*, lgShare*/, lgVideo],
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
    @include('modals.community.posts.addImages')
    @include('modals.community.posts.delete')
</div>
