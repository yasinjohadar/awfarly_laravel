@php
    $isSolved = $status === 'solved' || !empty($post->deleted_at);
    $authorName = optional($post->user)->name ?? '—';
    $authorType = optional($post->user)->user_type ? ucwords($post->user->user_type) : '—';
    $authorId = optional($post->user)->id ?? '—';
    $statusLabel = $isSolved
        ? __('pages/community/posts/reports/show.content.solved')
        : __('pages/community/posts/reports/show.content.unsolved');
@endphp

<div class="post-report-show" wire:key="post-report-show-{{ $post_id }}">
    <style>
        .post-report-show {
            --prs-accent: #c62828;
            --prs-accent-soft: rgba(198, 40, 40, 0.08);
            --prs-blue: #1565c0;
            --prs-success: #2e7d32;
            --prs-warning: #ef6c00;
            --prs-muted: #607d8b;
            --prs-border: #e3e8ef;
            --prs-card: #fff;
            --prs-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .post-report-show__toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .post-report-show__toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .post-report-show__hero {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.16), transparent 42%),
                linear-gradient(135deg, #ef5350 0%, #c62828 55%, #b71c1c 100%);
            box-shadow: var(--prs-shadow);
        }

        .post-report-show__hero.is-solved {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.12), transparent 42%),
                linear-gradient(135deg, #66bb6a 0%, #43a047 55%, #2e7d32 100%);
        }

        .post-report-show__hero::after {
            content: "";
            position: absolute;
            inset: auto -2rem -3rem auto;
            width: 11rem;
            height: 11rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .post-report-show__hero-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .post-report-show__eyebrow {
            margin: 0 0 .4rem;
            opacity: .88;
            font-size: .85rem;
        }

        .post-report-show__hero h3 {
            margin: 0 0 .35rem;
            font-size: 1.45rem;
            font-weight: 700;
            max-width: 42rem;
            line-height: 1.35;
        }

        .post-report-show__hero-meta {
            margin: 0;
            opacity: .9;
            font-size: .92rem;
        }

        .post-report-show__badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        .post-report-show__badge {
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

        .post-report-show__id-block {
            text-align: end;
            min-width: 7rem;
            position: relative;
            z-index: 1;
        }

        .post-report-show__id-label {
            opacity: .8;
            font-size: .8rem;
        }

        .post-report-show__id-value {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .post-report-show__metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .post-report-show__metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .post-report-show__metric {
            background: var(--prs-card);
            border: 1px solid var(--prs-border);
            border-radius: .9rem;
            padding: 1rem 1.1rem;
            box-shadow: var(--prs-shadow);
        }

        .post-report-show__metric-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            color: var(--prs-muted);
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        .post-report-show__metric-value {
            font-size: 1.35rem;
            font-weight: 800;
            color: #263238;
        }

        .post-report-show__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 991px) {
            .post-report-show__grid {
                grid-template-columns: 1fr;
            }
        }

        .post-report-show__panel {
            background: var(--prs-card);
            border: 1px solid var(--prs-border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            box-shadow: var(--prs-shadow);
            margin-bottom: 1.25rem;
        }

        .post-report-show__panel-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0 0 1rem;
            font-size: 1rem;
            font-weight: 700;
            color: #263238;
        }

        .post-report-show__panel-title i {
            color: var(--prs-accent);
        }

        .post-report-show__fields {
            display: grid;
            gap: .75rem;
        }

        .post-report-show__field {
            display: grid;
            gap: .2rem;
            padding: .8rem 1rem;
            border-radius: .75rem;
            background: #f7f9fc;
            border: 1px solid #eef2f7;
        }

        .post-report-show__field-label {
            color: var(--prs-muted);
            font-size: .78rem;
            font-weight: 600;
        }

        .post-report-show__field-value {
            color: #263238;
            font-size: .98rem;
            font-weight: 700;
            word-break: break-word;
        }

        .post-report-show__content {
            margin: 0;
            white-space: pre-wrap;
            line-height: 1.8;
            color: #37474f;
            font-size: 1.02rem;
            background: linear-gradient(180deg, #fff8f8 0%, #fff 100%);
            border: 1px dashed #ffcdd2;
            border-radius: .85rem;
            padding: 1.1rem 1.2rem;
        }

        .post-report-show__media {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .post-report-show__media li {
            width: 160px;
        }

        .post-report-show__media img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: .75rem;
            border: 1px solid var(--prs-border);
        }

        .post-report-show__media-caption {
            margin-top: .35rem;
            text-align: center;
            font-size: .8rem;
            font-weight: 600;
            color: var(--prs-muted);
        }
    </style>

    <div class="post-report-show__toolbar">
        <button type="button" class="btn btn-secondary"
                wire:click="$emitUp('setPostId', null)">
            <i class="icon-arrow-right13 mr-1"></i>
            {{ __('pages/community/posts/reports/show.content.back') }}
        </button>

        <div class="post-report-show__toolbar-actions">
            @if(!$isSolved)
                <button type="button"
                        class="btn btn-primary"
                        @cannot('posts.reported') disabled @endcannot
                        wire:click="showSolveModal({{ $post_id }})">
                    <i class="icon-checkmark3 mr-1"></i>
                    {{ __('pages/community/posts/reports/show.content.solve') }}
                </button>
            @endif

            @if(!$post->deleted_at && !$isSolved)
                <button type="button"
                        class="btn btn-danger"
                        @cannot('posts.delete') disabled @endcannot
                        wire:click="showDeleteModal({{ $post_id }})">
                    <i class="icon-trash mr-1"></i>
                    {{ __('pages/community/posts/reports/show.content.delete') }}
                </button>
            @endif
        </div>
    </div>

    <div class="post-report-show__hero{{ $isSolved ? ' is-solved' : '' }}">
        <div class="post-report-show__hero-top">
            <div>
                <p class="post-report-show__eyebrow">
                    {!! __('pages/community/posts/reports/show.content.title', ['id' => $post->id]) !!}
                </p>
                <h3>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 90) }}</h3>
                <p class="post-report-show__hero-meta">
                    <i class="icon-user mr-1"></i>{{ $authorName }}
                    <span class="mx-1">·</span>
                    <i class="icon-calendar22 mr-1"></i>{{ $post->created_at }}
                </p>
            </div>
            <div class="post-report-show__id-block">
                <div class="post-report-show__id-label">#</div>
                <div class="post-report-show__id-value">{{ $post->id }}</div>
            </div>
        </div>

        <div class="post-report-show__badges">
            <span class="post-report-show__badge">
                <i class="icon-warning22"></i>
                {{ $statusLabel }}
            </span>
            <span class="post-report-show__badge">
                <i class="icon-bubbles5"></i>
                {{ __('pages/community/posts/reports/show.content.reports_count') }}: {{ $reports_count }}
            </span>
            @if($post->deleted_at)
                <span class="post-report-show__badge">
                    <i class="icon-trash"></i>
                    {{ __('pages/community/posts/reports/show.content.deleted_at') }}: {{ $post->deleted_at }}
                </span>
            @endif
        </div>
    </div>

    <div class="post-report-show__metrics">
        <div class="post-report-show__metric">
            <div class="post-report-show__metric-label">
                <i class="icon-eye"></i>
                {{ __('pages/community/posts/reports/show.content.views_count') }}
            </div>
            <div class="post-report-show__metric-value">{{ $post->views_count ?? 0 }}</div>
        </div>
        <div class="post-report-show__metric">
            <div class="post-report-show__metric-label">
                <i class="icon-heart5"></i>
                {{ __('pages/community/posts/reports/show.content.likes_count') }}
            </div>
            <div class="post-report-show__metric-value">{{ $post->likes_count ?? 0 }}</div>
        </div>
        <div class="post-report-show__metric">
            <div class="post-report-show__metric-label">
                <i class="icon-comment"></i>
                {{ __('pages/community/posts/reports/show.content.comments_count') }}
            </div>
            <div class="post-report-show__metric-value">{{ $post->comments_count ?? 0 }}</div>
        </div>
        <div class="post-report-show__metric">
            <div class="post-report-show__metric-label">
                <i class="icon-warning22"></i>
                {{ __('pages/community/posts/reports/show.content.reports_count') }}
            </div>
            <div class="post-report-show__metric-value">{{ $reports_count }}</div>
        </div>
    </div>

    <div class="post-report-show__grid">
        <div class="post-report-show__panel mb-0">
            <h5 class="post-report-show__panel-title">
                <i class="icon-user"></i>
                {{ __('pages/community/posts/reports/show.content.sections.author') }}
            </h5>
            <div class="post-report-show__fields">
                <div class="post-report-show__field">
                    <div class="post-report-show__field-label">{{ __('pages/community/posts/reports/show.content.user_name') }}</div>
                    <div class="post-report-show__field-value">{{ $authorName }}</div>
                </div>
                <div class="post-report-show__field">
                    <div class="post-report-show__field-label">{{ __('pages/community/posts/reports/show.content.user_type') }}</div>
                    <div class="post-report-show__field-value">{{ $authorType }}</div>
                </div>
                <div class="post-report-show__field">
                    <div class="post-report-show__field-label">{{ __('pages/community/posts/reports/show.content.user_id') }}</div>
                    <div class="post-report-show__field-value">{{ $authorId }}</div>
                </div>
            </div>
        </div>

        <div class="post-report-show__panel mb-0">
            <h5 class="post-report-show__panel-title">
                <i class="icon-file-text2"></i>
                {{ __('pages/community/posts/reports/show.content.sections.content') }}
            </h5>
            <div class="post-report-show__content">
                {!! nl2br(e(strip_tags($post->content))) !!}
            </div>
        </div>
    </div>

    @if(count($post['media']) > 0)
        <div class="post-report-show__panel">
            <h5 class="post-report-show__panel-title">
                <i class="icon-images3"></i>
                {{ __('pages/community/posts/reports/show.content.sections.media') }}
            </h5>
            <ul id="animated-thumbnails-gallery" dir="ltr" class="post-report-show__media">
                @foreach ($post['media'] as $index => $media)
                    <li data-id="{{ $media['id'] }}" data-src="{{ $media['mediaUrl'] }}"
                        data-download-url="{{ $media['downloadUrl'] }}">
                        @if($media['type'] === 'video')
                            <a data-lg-size="1920-1080"
                               data-video='{"source": [{"src":"{{ $media['mediaUrl'] }}", "type":"{{ $media['mimeType'] }}"}], "attributes": {"preload": false, "controls": true}}'
                               data-poster="{{ $media['thumbnailImageUrl'] }}"
                               data-sub-html="{{ $media['fileName'] }}">
                                <img src="{{ $media['thumbnailImageUrl'] ?? $media['mediaUrl'] }}"
                                     alt="{{ $media['fileName'] }}"/>
                            </a>
                        @else
                            <a href="{{ $media['mediaUrl'] }}" data-thumb="{{ $media['thumbnailImageUrl'] }}">
                                <img src="{{ $media['thumbnailImageUrl'] ?? $media['mediaUrl'] }}"
                                     alt="{{ $media['fileName'] }}"/>
                            </a>
                        @endif
                        <div class="post-report-show__media-caption">
                            {{ __('pages/community/posts/reports/show.content.image') }}{{ $index + 1 }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="post-report-show__panel">
        <h5 class="post-report-show__panel-title">
            <i class="icon-list"></i>
            {{ __('pages/community/posts/reports/show.content.sections.reports') }}
        </h5>
        @livewire('community.posts.reports.community-reported-post-show-component', ['post_id' => $post_id], key($post_id))
    </div>

    @if(count($post['media']) > 0)
        <script type="text/javascript">
            lightGallery(document.getElementById('animated-thumbnails-gallery'), {
                plugins: [lgZoom, lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager, lgRotate, lgShare, lgVideo],
                thumbnail: true,
                animateThumb: true,
                zoomFromOrigin: true,
                allowMediaOverlap: true,
                toggleThumb: true,
                hash: true,
                closable: true,
                showMaximizeIcon: false,
                slideDelay: 400,
                autoplayFirstVideo: false,
                getCaptionFromTitleOrAlt: true,
            });
        </script>
    @endif

    @include('modals.community.posts.reports.delete')
    @include('modals.community.posts.reports.solve')
</div>
