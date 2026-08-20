@extends('admin.layouts.app')

@php
    $name_column = App::getLocale() === 'ar' ? 'name_ar' : 'name_en';
    $category_name = $category->{$name_column} ?: $category->name_en;
    $is_main = !$category->parent_category_id;
    $base = 'pages/categories/show.content';
@endphp

@section('title', __('pages/categories/show.breadcrumb.title'))

@section('breadcrumbs')
    <a href="{{ route('admin.categories.index') }}" class="breadcrumb-item">
        {{ __('pages/categories/show.breadcrumb.categories') }}
    </a>
    @if($category->parentCategory)
        <a href="{{ route('admin.categories.show', $category->parentCategory->id) }}" class="breadcrumb-item">
            {{ $category->parentCategory->{$name_column} ?: $category->parentCategory->name_en }}
        </a>
    @endif
    <span class="breadcrumb-item active">{{ $category_name }}</span>
@endsection

@section('content')
    <style>
        .cat-show__hero {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cat-show__image {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1rem;
            object-fit: cover;
            border: 1px solid #dde5ee;
            background: #f1f5f9;
        }

        .cat-show__image-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #90a4ae;
            font-size: 1.6rem;
        }

        .cat-show__heading {
            margin: 0 0 .35rem;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
        }

        .cat-show__badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .cat-show__grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
        }

        @media (max-width: 991px) {
            .cat-show__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .cat-show__grid {
                grid-template-columns: 1fr;
            }
        }

        .cat-show__stat {
            position: relative;
            padding: .95rem 1rem;
            border-radius: .85rem;
            border: 1.5px solid #dde5ee;
            background: #fff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        .cat-show__stat::before {
            content: "";
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            width: 100%;
            height: 4px;
            background: #64748b;
        }

        .cat-show__stat--teal::before { background: #0d9488; }
        .cat-show__stat--blue::before { background: #2563eb; }
        .cat-show__stat--amber::before { background: #f59e0b; }
        .cat-show__stat--violet::before { background: #7c3aed; }
        .cat-show__stat--green::before { background: #16a34a; }
        .cat-show__stat--rose::before { background: #e11d48; }
        .cat-show__stat--slate::before { background: #64748b; }

        .cat-show__stat-value {
            display: block;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }

        .cat-show__stat-label {
            display: block;
            margin-top: .25rem;
            font-size: .8rem;
            font-weight: 600;
            color: #546e7a;
        }

        .cat-show__stat-hint {
            display: block;
            font-size: .7rem;
            color: #90a4ae;
        }

        .cat-show__info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .65rem 1.5rem;
        }

        @media (max-width: 767px) {
            .cat-show__info {
                grid-template-columns: 1fr;
            }
        }

        .cat-show__info-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: .5rem;
            border-bottom: 1px dashed #e3e8ef;
        }

        .cat-show__info-key {
            font-size: .8rem;
            font-weight: 600;
            color: #78909c;
        }

        .cat-show__info-value {
            font-size: .85rem;
            font-weight: 600;
            color: #263238;
            text-align: end;
            word-break: break-word;
        }
    </style>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap: .75rem">
            <div class="cat-show__hero">
                @if($category->image)
                    <a href="{{ route('category.image.get', $category->image) }}" target="_blank">
                        <img src="{{ route('category.image.get', $category->image) }}" class="cat-show__image"
                             alt="{{ $category_name }}"/>
                    </a>
                @else
                    <span class="cat-show__image cat-show__image-placeholder"><i class="icon-folder"></i></span>
                @endif
                <div>
                    <h5 class="cat-show__heading">
                        {{ $is_main ? __("$base.title_main", ['name' => $category_name]) : __("$base.title_sub", ['name' => $category_name]) }}
                    </h5>
                    <div class="cat-show__badges">
                        <span class="badge badge-{{ $is_main ? 'primary' : 'info' }}">
                            {{ $is_main ? __("$base.main_category") : __("$base.sub_category") }}
                        </span>
                        <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                            {{ $category->is_active ? __("$base.active") : __("$base.inactive") }}
                        </span>
                        <span class="badge badge-secondary">#{{ $category->id }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap" style="gap: .5rem">
                @if($category->parentCategory)
                    <a href="{{ route('admin.categories.show', $category->parentCategory->id) }}"
                       class="btn btn-outline-secondary">
                        <i class="icon-arrow-up12 mr-1"></i>
                        {{ __("$base.info.view_parent") }}
                    </a>
                @endif
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    {{ __("$base.back") }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <h6 class="font-weight-bold mb-3">{{ __("$base.info.title") }}</h6>
            <div class="cat-show__info mb-4">
                <div class="cat-show__info-row">
                    <span class="cat-show__info-key">{{ __("$base.info.name_ar") }}</span>
                    <span class="cat-show__info-value">{{ $category->name_ar ?: __("$base.info.empty") }}</span>
                </div>
                <div class="cat-show__info-row">
                    <span class="cat-show__info-key">{{ __("$base.info.name_en") }}</span>
                    <span class="cat-show__info-value">{{ $category->name_en ?: __("$base.info.empty") }}</span>
                </div>
                <div class="cat-show__info-row">
                    <span class="cat-show__info-key">{{ __("$base.info.parent") }}</span>
                    <span class="cat-show__info-value">
                        @if($category->parentCategory)
                            <a href="{{ route('admin.categories.show', $category->parentCategory->id) }}">
                                {{ $category->parentCategory->{$name_column} ?: $category->parentCategory->name_en }}
                            </a>
                        @else
                            {{ __("$base.info.empty") }}
                        @endif
                    </span>
                </div>
                <div class="cat-show__info-row">
                    <span class="cat-show__info-key">{{ __("$base.info.order") }}</span>
                    <span class="cat-show__info-value">{{ $category->order ?? __("$base.info.empty") }}</span>
                </div>
                <div class="cat-show__info-row">
                    <span class="cat-show__info-key">{{ __("$base.info.created_at") }}</span>
                    <span class="cat-show__info-value">
                        {{ $category->created_at ? $category->created_at->format('Y-m-d h:i A') : __("$base.info.empty") }}
                    </span>
                </div>
                <div class="cat-show__info-row">
                    <span class="cat-show__info-key">{{ __("$base.info.updated_at") }}</span>
                    <span class="cat-show__info-value">
                        {{ $category->updated_at ? $category->updated_at->format('Y-m-d h:i A') : __("$base.info.empty") }}
                    </span>
                </div>
                <div class="cat-show__info-row" style="grid-column: 1 / -1">
                    <span class="cat-show__info-key">{{ __("$base.info.description") }}</span>
                    <span class="cat-show__info-value">{{ $category->description ?: __("$base.info.empty") }}</span>
                </div>
            </div>

            <h6 class="font-weight-bold mb-3">{{ __("$base.stats.title") }}</h6>
            <div class="cat-show__grid">
                @if($is_main)
                    <div class="cat-show__stat cat-show__stat--violet">
                        <span class="cat-show__stat-value">{{ number_format($statistics['sub_categories']) }}</span>
                        <span class="cat-show__stat-label">{{ __("$base.stats.sub_categories") }}</span>
                    </div>
                @endif
                <div class="cat-show__stat cat-show__stat--teal">
                    <span class="cat-show__stat-value">{{ number_format($statistics['advertisers_total']) }}</span>
                    <span class="cat-show__stat-label">{{ __("$base.stats.advertisers_total") }}</span>
                    @if($is_main)
                        <span class="cat-show__stat-hint">{{ __("$base.stats.advertisers_total_hint") }}</span>
                    @endif
                </div>
                @if($is_main)
                    <div class="cat-show__stat cat-show__stat--blue">
                        <span class="cat-show__stat-value">{{ number_format($statistics['advertisers_direct']) }}</span>
                        <span class="cat-show__stat-label">{{ __("$base.stats.advertisers_direct") }}</span>
                    </div>
                @endif
                <div class="cat-show__stat cat-show__stat--green">
                    <span class="cat-show__stat-value">{{ number_format($statistics['advertisers_active']) }}</span>
                    <span class="cat-show__stat-label">{{ __("$base.stats.advertisers_active") }}</span>
                </div>
                <div class="cat-show__stat cat-show__stat--amber">
                    <span class="cat-show__stat-value">{{ number_format($statistics['advertisers_elite']) }}</span>
                    <span class="cat-show__stat-label">{{ __("$base.stats.advertisers_elite") }}</span>
                </div>
                <div class="cat-show__stat cat-show__stat--rose">
                    <span class="cat-show__stat-value">{{ number_format($statistics['offers']) }}</span>
                    <span class="cat-show__stat-label">{{ __("$base.stats.offers") }}</span>
                </div>
                <div class="cat-show__stat cat-show__stat--slate">
                    <span class="cat-show__stat-value">{{ number_format($statistics['posts']) }}</span>
                    <span class="cat-show__stat-label">{{ __("$base.stats.posts") }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($sub_categories->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __("$base.sub_categories.title") }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __("$base.sub_categories.name") }}</th>
                            <th class="text-center">{{ __("$base.sub_categories.advertisers") }}</th>
                            <th class="text-center">{{ __("$base.sub_categories.offers") }}</th>
                            <th class="text-center">{{ __("$base.sub_categories.posts") }}</th>
                            <th class="text-center">{{ __("$base.sub_categories.status") }}</th>
                            <th class="text-center">{{ __("$base.sub_categories.actions") }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sub_categories as $sub_category)
                            <tr>
                                <td>{{ $sub_category->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center" style="gap: .5rem">
                                        @if($sub_category->image)
                                            <img src="{{ route('category.image.get', $sub_category->image) }}"
                                                 class="rounded-circle" height="32" width="32"
                                                 alt="{{ $sub_category->{$name_column} }}"/>
                                        @endif
                                        <span class="font-weight-semibold">
                                            {{ $sub_category->{$name_column} ?: $sub_category->name_en }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $sub_category->advertisers_count }}</span>
                                </td>
                                <td class="text-center">{{ $sub_category->offers_count }}</td>
                                <td class="text-center">{{ $sub_category->posts_count }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $sub_category->is_active ? 'success' : 'danger' }}">
                                        {{ $sub_category->is_active ? __("$base.active") : __("$base.inactive") }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.categories.show', $sub_category->id) }}"
                                       class="btn btn-sm btn-secondary">
                                        {{ __("$base.sub_categories.view") }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($is_main)
        <div class="alert alert-secondary">{{ __("$base.sub_categories.empty") }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-1">{{ __("$base.advertisers.title") }}</h5>
            <span class="text-muted small">
                {{ $is_main ? __("$base.advertisers.subtitle_main") : __("$base.advertisers.subtitle_sub") }}
            </span>
        </div>
        <div class="card-body">
            @livewire('categories.category-advertisers-component', ['category_id' => $category->id], key('category-advertisers-' . $category->id))
        </div>
    </div>
@endsection
