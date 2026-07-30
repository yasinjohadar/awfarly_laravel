@extends('admin.layouts.app')

@section('title', __('pages/dashboard/index.breadcrumb.title'))

@section('content')
    <style>
        .dash {
            --dash-text: #0f172a;
            --dash-muted: #64748b;
            --dash-gap: 1.1rem;
        }

        .dash-section.card {
            background: #fff;
            border: 1px solid #d7e0ea;
            border-radius: 1.15rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .dash-section__header.card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.05rem 1.25rem;
            border-bottom: 1px solid #dbe4ee;
            background: #fff;
        }

        .dash-section__title {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin: 0;
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--dash-text);
        }

        .dash-section__title-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: .75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
        }

        .dash-section__title-icon.is-users { background: #1565c0; }
        .dash-section__title-icon.is-requests { background: #00695c; }
        .dash-section__title-icon.is-community { background: #00695c; }
        .dash-section__title-icon.is-reports { background: #c62828; }
        .dash-section__title-icon.is-payments { background: #2e7d32; }
        .dash-section__title-icon.is-map { background: #3949ab; }
        .dash-section__title-icon.is-packages { background: #ef6c00; }
        .dash-section__title-icon.is-charts { background: #455a64; }

        .dash-section__body {
            padding: 1.15rem;
            background: #eef2f7;
        }

        .dash-group {
            background: #fff;
            border: 1px solid #d5dee8;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .dash-group + .dash-group {
            margin-top: 1rem;
        }

        .dash-subsection {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 0 0 .95rem;
            color: #334155;
            font-size: .86rem;
            font-weight: 800;
        }

        .dash-subsection::before {
            content: "";
            width: .45rem;
            height: .45rem;
            border-radius: 999px;
            background: #0d9488;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15);
            flex-shrink: 0;
        }

        .dash-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--dash-gap);
        }

        .dash-grid--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        @media (max-width: 991px) {
            .dash-grid,
            .dash-grid--2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .dash-grid,
            .dash-grid--2 {
                grid-template-columns: 1fr;
            }
        }

        .dash-stat {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: .55rem;
            padding: 1.05rem 1.05rem 1.15rem;
            border-radius: .95rem;
            text-decoration: none !important;
            border: 1.5px solid #d8e0ea;
            background: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            overflow: hidden;
            min-height: 8.2rem;
        }

        .dash-stat::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: .3rem;
        }

        .dash-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.12);
            border-color: #94a3b8;
            text-decoration: none !important;
        }

        .dash-stat__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .dash-stat__icon {
            width: 2.65rem;
            height: 2.65rem;
            border-radius: .8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.15rem;
            color: #fff;
        }

        .dash-stat__value {
            color: var(--dash-text);
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.03em;
            margin-top: .15rem;
        }

        .dash-stat__value small {
            margin-inline-start: .3rem;
            font-size: .78rem;
            font-weight: 700;
            color: var(--dash-muted);
        }

        .dash-stat__label {
            color: var(--dash-muted);
            font-size: .88rem;
            font-weight: 700;
            line-height: 1.35;
            margin-top: auto;
        }

        .dash-stat__go {
            width: 1.85rem;
            height: 1.85rem;
            border-radius: .55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
            transition: background .18s ease, color .18s ease, transform .18s ease;
            flex-shrink: 0;
        }

        .dash-stat:hover .dash-stat__go {
            transform: translateX(2px);
        }

        [dir="rtl"] .dash-stat:hover .dash-stat__go {
            transform: translateX(-2px);
        }

        .dash-stat--teal::before { background: #0d9488; }
        .dash-stat--teal .dash-stat__icon { background: #0d9488; }
        .dash-stat--teal:hover { border-color: #0d9488; }
        .dash-stat--teal:hover .dash-stat__go { background: #0d9488; color: #fff; }

        .dash-stat--amber::before { background: #f59e0b; }
        .dash-stat--amber .dash-stat__icon { background: #f59e0b; }
        .dash-stat--amber:hover { border-color: #f59e0b; }
        .dash-stat--amber:hover .dash-stat__go { background: #f59e0b; color: #fff; }

        .dash-stat--blue::before { background: #2563eb; }
        .dash-stat--blue .dash-stat__icon { background: #2563eb; }
        .dash-stat--blue:hover { border-color: #2563eb; }
        .dash-stat--blue:hover .dash-stat__go { background: #2563eb; color: #fff; }

        .dash-stat--violet::before { background: #7c3aed; }
        .dash-stat--violet .dash-stat__icon { background: #7c3aed; }
        .dash-stat--violet:hover { border-color: #7c3aed; }
        .dash-stat--violet:hover .dash-stat__go { background: #7c3aed; color: #fff; }

        .dash-stat--rose::before { background: #e11d48; }
        .dash-stat--rose .dash-stat__icon { background: #e11d48; }
        .dash-stat--rose:hover { border-color: #e11d48; }
        .dash-stat--rose:hover .dash-stat__go { background: #e11d48; color: #fff; }

        .dash-stat--slate::before { background: #64748b; }
        .dash-stat--slate .dash-stat__icon { background: #64748b; }
        .dash-stat--slate:hover { border-color: #64748b; }
        .dash-stat--slate:hover .dash-stat__go { background: #64748b; color: #fff; }

        .dash-stat--green::before { background: #16a34a; }
        .dash-stat--green .dash-stat__icon { background: #16a34a; }
        .dash-stat--green:hover { border-color: #16a34a; }
        .dash-stat--green:hover .dash-stat__go { background: #16a34a; color: #fff; }

        .dash-chart-wrap,
        .dash-map-wrap {
            border: 1.5px solid #d5dee8;
            border-radius: .95rem;
            padding: .85rem;
            background: #fff;
        }
    </style>

    <div class="dash">
        @canany(['advertisers.inquiry', 'customers.inquiry'])
            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-users"><i class="icon-users"></i></span>
                        {{ __('pages/dashboard/index.content.users_statistics.title') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>

                <div class="card-body collapse show dash-section__body">
                        @can('advertisers.inquiry')
                            <div class="dash-group">
                                <p class="dash-subsection">{{ __('pages/dashboard/index.content.users_statistics.total_advertisers') }}</p>
                                <div class="dash-grid">
                                    <x-dashboard-stat
                                        :href="route('admin.advertisers.index')"
                                        :label="__('pages/dashboard/index.content.users_statistics.new_advertisers')"
                                        :value="$advertisers_counters['new']"
                                        icon="icon-user-plus"
                                        tone="teal"
                                    />
                                    <x-dashboard-stat
                                        :href="route('admin.advertisers.index')"
                                        :label="__('pages/dashboard/index.content.users_statistics.total_advertisers')"
                                        :value="$advertisers_counters['total']"
                                        icon="icon-briefcase"
                                        tone="amber"
                                    />
                                    <x-dashboard-stat
                                        :href="route('admin.advertisers.index')"
                                        :label="__('pages/dashboard/index.content.users_statistics.elite_advertisers')"
                                        :value="$advertisers_counters['elite']"
                                        icon="icon-medal-star"
                                        tone="blue"
                                    />
                                </div>
                            </div>
                        @endcan

                        @can('customers.inquiry')
                            <div class="dash-group">
                                <p class="dash-subsection">{{ __('pages/dashboard/index.content.users_statistics.total_customers') }}</p>
                                <div class="dash-grid">
                                    <x-dashboard-stat
                                        :href="route('admin.customers.index')"
                                        :label="__('pages/dashboard/index.content.users_statistics.new_customers')"
                                        :value="$customers_counters['new']"
                                        icon="icon-user-plus"
                                        tone="teal"
                                    />
                                    <x-dashboard-stat
                                        :href="route('admin.customers.index')"
                                        :label="__('pages/dashboard/index.content.users_statistics.total_customers')"
                                        :value="$customers_counters['total']"
                                        icon="icon-users4"
                                        tone="amber"
                                    />
                                    <x-dashboard-stat
                                        :href="route('admin.customers.index')"
                                        :label="__('pages/dashboard/index.content.users_statistics.active_customers')"
                                        :value="$customers_counters['active']"
                                        icon="icon-checkmark-circle"
                                        tone="green"
                                    />
                                </div>
                            </div>
                        @endcan
                </div>
            </section>
        @endcanany

        @canany(['statistics.requests'])
            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-requests"><i class="icon-mailbox"></i></span>
                        {{ __('pages/dashboard/index.content.requests_statistics.title') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body collapse show dash-section__body">
                    <div class="dash-group">
                        <div class="dash-grid dash-grid--2">
                            <x-dashboard-stat
                                :href="route('admin.requests.contact-us.index')"
                                :label="__('pages/dashboard/index.content.requests_statistics.contact-us')"
                                :value="$requests_counters['contact-us']"
                                icon="icon-envelop2"
                                tone="teal"
                            />
                            <x-dashboard-stat
                                :href="route('admin.requests.change-name.index')"
                                :label="__('pages/dashboard/index.content.requests_statistics.username-change')"
                                :value="$requests_counters['username-change']"
                                icon="icon-pencil7"
                                tone="amber"
                            />
                        </div>
                    </div>
                </div>
            </section>
        @endcanany

        @canany(['statistics.reports'])
            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-community"><i class="icon-collaboration"></i></span>
                        {{ __('pages/dashboard/index.content.community_statistics.title') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body collapse show dash-section__body">
                    <div class="dash-group">
                        <div class="dash-grid">
                            <x-dashboard-stat
                                :href="route('admin.community.posts.index')"
                                :label="__('pages/dashboard/index.content.community_statistics.posts')"
                                :value="$community_counters['posts']"
                                icon="icon-file-text2"
                                tone="teal"
                            />
                            <x-dashboard-stat
                                :href="route('admin.community.offers.index')"
                                :label="__('pages/dashboard/index.content.community_statistics.offers')"
                                :value="$community_counters['offers']"
                                icon="icon-price-tags2"
                                tone="amber"
                            />
                            <x-dashboard-stat
                                :href="route('admin.community.proposals.index')"
                                :label="__('pages/dashboard/index.content.community_statistics.proposals')"
                                :value="$community_counters['proposals']"
                                icon="icon-clipboard3"
                                tone="violet"
                            />
                        </div>
                    </div>
                    <div class="dash-group">
                        <div class="dash-grid dash-grid--2">
                            <x-dashboard-stat
                                :href="route('admin.community.comments.index')"
                                :label="__('pages/dashboard/index.content.community_statistics.posts-comments')"
                                :value="$community_counters['posts_comments']"
                                icon="icon-bubbles5"
                                tone="blue"
                            />
                            <x-dashboard-stat
                                :href="route('admin.community.offers.comments.index')"
                                :label="__('pages/dashboard/index.content.community_statistics.offers-comments')"
                                :value="$community_counters['offers_comments']"
                                icon="icon-bubbles4"
                                tone="slate"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-reports"><i class="icon-warning22"></i></span>
                        {{ __('pages/dashboard/index.content.reports_statistics.title') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body collapse show dash-section__body">
                    <div class="dash-group">
                        <div class="dash-grid">
                            <x-dashboard-stat
                                :href="route('admin.community.reports.posts')"
                                :label="__('pages/dashboard/index.content.reports_statistics.posts')"
                                :value="$reports_counters['posts']"
                                icon="icon-flag3"
                                tone="rose"
                            />
                            <x-dashboard-stat
                                :href="route('admin.community.reports.offers')"
                                :label="__('pages/dashboard/index.content.reports_statistics.offers')"
                                :value="$reports_counters['offers']"
                                icon="icon-flag4"
                                tone="amber"
                            />
                            <x-dashboard-stat
                                :href="route('admin.community.reports.proposals')"
                                :label="__('pages/dashboard/index.content.reports_statistics.proposals')"
                                :value="$reports_counters['proposals']"
                                icon="icon-flag7"
                                tone="violet"
                            />
                        </div>
                    </div>
                    <div class="dash-group">
                        <div class="dash-grid dash-grid--2">
                            <x-dashboard-stat
                                :href="route('admin.community.reports.comments')"
                                :label="__('pages/dashboard/index.content.reports_statistics.posts-comments')"
                                :value="$reports_counters['posts_comments']"
                                icon="icon-bubble-notification"
                                tone="blue"
                            />
                            <x-dashboard-stat
                                :href="route('admin.community.offers.comments.reports')"
                                :label="__('pages/dashboard/index.content.reports_statistics.offers-comments')"
                                :value="$reports_counters['offers_comments']"
                                icon="icon-bubble-lines4"
                                tone="slate"
                            />
                        </div>
                    </div>
                </div>
            </section>
        @endcanany

        @canany(['statistics.users'])
            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-map"><i class="icon-location4"></i></span>
                        {{ __('pages/dashboard/index.content.users_onMap.title') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body collapse show dash-section__body">
                    <div class="dash-group">
                        <div class="dash-map-wrap" style="border:0;padding:0;background:transparent;">
                            <div class="map-container map-world-users"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endcanany

        @canany(['statistics.payments'])
            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-payments"><i class="icon-cash4"></i></span>
                        {{ __('pages/dashboard/index.content.transactions_statistics.title') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body collapse show dash-section__body">
                    <div class="dash-group">
                        <div class="dash-grid">
                            <x-dashboard-stat
                                :href="route('admin.subscriptions.payments.index')"
                                :label="__('pages/dashboard/index.content.transactions_statistics.new')"
                                :value="$transactions_counters['new']"
                                :suffix="__('pages/dashboard/index.content.transactions_statistics.SAR')"
                                icon="icon-coin-dollar"
                                tone="teal"
                            />
                            <x-dashboard-stat
                                :href="route('admin.subscriptions.payments.index')"
                                :label="__('pages/dashboard/index.content.transactions_statistics.total')"
                                :value="$transactions_counters['total']"
                                :suffix="__('pages/dashboard/index.content.transactions_statistics.SAR')"
                                icon="icon-cash3"
                                tone="green"
                            />
                            <x-dashboard-stat
                                :href="route('admin.subscriptions.payments.index')"
                                :label="__('pages/dashboard/index.content.transactions_statistics.ended')"
                                :value="$transactions_counters['ended']"
                                icon="icon-calendar52"
                                tone="amber"
                            />
                        </div>
                        <div class="dash-chart-wrap mt-3">
                            <div class="chart has-fixed-height" id="transactions_counter"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endcanany

        @canany(['statistics.packages'])
            <section class="card dash-section">
                <div class="card-header dash-section__header header-elements-inline">
                    <h5 class="dash-section__title">
                        <span class="dash-section__title-icon is-packages"><i class="icon-stack2"></i></span>
                        {{ __('pages/dashboard/index.content.packages_statistics') }}
                    </h5>
                    <div class="header-elements">
                        <div class="list-icons">
                            <a class="list-icons-item" data-action="collapse"></a>
                        </div>
                    </div>
                </div>
                <div class="card-body collapse show dash-section__body">
                    <div class="dash-group">
                        <div class="dash-chart-wrap" style="border:0;padding:0;background:transparent;">
                            <div class="chart has-fixed-height" id="packages_statistics"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endcanany

        @canany(['statistics.proposals', 'statistics.users'])
            <div class="row">
                @can('statistics.proposals')
                    <div class="col-xl-6">
                        <section class="card dash-section">
                            <div class="card-header dash-section__header header-elements-inline">
                                <h5 class="dash-section__title">
                                    <span class="dash-section__title-icon is-charts"><i class="icon-pie-chart5"></i></span>
                                    {{ __('pages/dashboard/index.content.proposals_statistics.title') }}
                                </h5>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="collapse"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body collapse show dash-section__body">
                                <div class="dash-group">
                                    <div class="dash-chart-wrap" style="border:0;padding:0;background:transparent;">
                                        <div class="chart has-fixed-height" id="proposals_statistics"></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                @endcan
                @can('statistics.users')
                    <div class="col-xl-6">
                        <section class="card dash-section">
                            <div class="card-header dash-section__header header-elements-inline">
                                <h5 class="dash-section__title">
                                    <span class="dash-section__title-icon is-charts"><i class="icon-pie-chart8"></i></span>
                                    {{ __('pages/dashboard/index.content.users.title') }}
                                </h5>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="collapse"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body collapse show dash-section__body">
                                <div class="dash-group">
                                    <div class="dash-chart-wrap" style="border:0;padding:0;background:transparent;">
                                        <div class="chart has-fixed-height" id="users_statistics"></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                @endcan
            </div>
        @endcanany
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        /*Users Map*/
        $('.map-world-users').vectorMap({
            map: 'world_mill_en',
            backgroundColor: 'transparent',
            scaleColors: ['#C8EEFF', '#0071A4'],
            normalizeFunction: 'polynomial',
            regionStyle: {
                initial: {
                    fill: '#D6E1ED'
                }
            },
            hoverOpacity: 0.7,
            hoverColor: false,
            markerStyle: {
                initial: {
                    r: 7,
                    'fill': '#336BB5',
                    'fill-opacity': 0.8,
                    'stroke': '#fff',
                    'stroke-width': 1.5,
                    'stroke-opacity': 0.9
                },
                hover: {
                    'stroke': '#fff',
                    'fill-opacity': 1,
                    'stroke-width': 1.5
                }
            },
            focusOn: {
                x: 0.5,
                y: 0.5,
                scale: 1
            },
            markers: {!! json_encode($users_by_countries) !!},
        });


        /*Transactions Statistics*/
        let months = {
            'jan': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.jan')}}",
            'feb': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.feb')}}",
            'mar': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.mar')}}",
            'apr': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.apr')}}",
            'may': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.may')}}",
            'jun': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.jun')}}",
            'jul': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.jul')}}",
            'aug': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.aug')}}",
            'sep': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.sep')}}",
            'oct': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.oct')}}",
            'nov': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.nov')}}",
            'dec': "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.months.dec')}}",
        };
        echarts.init(document.getElementById('transactions_counter')).setOption({
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: [
                    "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.amount')}}",
                    "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.transactions')}}",
                    "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.advertisers')}}",
                ]
            },
            toolbox: {
                feature: {
                    saveAsImage: {}
                }
            },
            xAxis: {
                type: 'category',
                boundaryGap: true,
                data: [
                    @foreach(array_keys($transactions_counters['months']) as $month_key)
                        months["{{$month_key}}"],
                    @endforeach
                ]
            },
            yAxis: {
                type: 'value',
            },
            series: [
                {
                    name: "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.amount')}}",
                    type: 'line',
                    stack: "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.amount')}}",
                    smooth: true,
                    data: [
                            @foreach($transactions_counters['months'] as $index => $data)
                        {
                            value: {{$data['amount']}},
                        },
                        @endforeach
                    ],
                },
                {
                    name: "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.transactions')}}",
                    type: 'line',
                    stack: "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.transactions')}}",
                    smooth: true,
                    data: [
                            @foreach($transactions_counters['months'] as $index => $data)
                        {
                            value: {{$data['count']}},
                        },
                        @endforeach
                    ],
                },
                {
                    name: "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.advertisers')}}",
                    type: 'line',
                    stack: "{{__('pages/dashboard/index.content.transactions_statistics.chart.categories.advertisers')}}",
                    smooth: true,
                    data: [
                            @foreach($transactions_counters['months'] as $index => $data)
                        {
                            value: {{$data['advertisers']}},
                        },
                        @endforeach
                    ],
                },
            ]
        });

        /*Proposals*/
        let proposals = {!! json_encode($proposals) !!};

        echarts.init(document.getElementById('proposals_statistics')).setOption({
            color: [
                '#2ec7c9', '#b6a2de', '#5ab1ef', '#ffb980', '#d87a80',
                '#8d98b3', '#e5cf0d', '#97b552', '#95706d', '#dc69aa',
                '#07a2a4', '#9a7fd1', '#588dd5', '#f5994e', '#c05050',
                '#59678c', '#c9ab00', '#7eb00a', '#6f5553', '#c14089'
            ],
            textStyle: {
                fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                fontSize: 13
            },
            title: {
                text: "{{__('pages/dashboard/index.content.proposals_statistics.text')}}",
                subtext: "{{__('pages/dashboard/index.content.proposals_statistics.subText')}}",
                left: 'center',
                textStyle: {
                    fontSize: 17,
                    fontWeight: 500
                },
                subtextStyle: {
                    fontSize: 12
                }
            },
            tooltip: {
                trigger: 'item',
                backgroundColor: 'rgba(0,0,0,0.75)',
                padding: [10, 15],
                textStyle: {
                    fontSize: 13,
                    fontFamily: 'Roboto, sans-serif'
                },
                formatter: "{a} <br/>{b}: {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                top: 'center',
                left: 0,
                data: proposals.types,
                itemHeight: 8,
                itemWidth: 8
            },
            series: [{
                name: '{{__('pages/dashboard/index.content.proposals_statistics.text')}}',
                type: 'pie',
                radius: '70%',
                center: ['50%', '57.5%'],
                itemStyle: {
                    normal: {
                        borderWidth: 1,
                        borderColor: '#fff'
                    }
                },
                data: proposals.data
            }]
        });

        /*Customers to advertisers*/
        let users = {!! json_encode($users) !!};

        echarts.init(document.getElementById('users_statistics')).setOption({
            color: [
                '#2ec7c9', '#b6a2de', '#5ab1ef', '#ffb980', '#d87a80',
                '#8d98b3', '#e5cf0d', '#97b552', '#95706d', '#dc69aa',
                '#07a2a4', '#9a7fd1', '#588dd5', '#f5994e', '#c05050',
                '#59678c', '#c9ab00', '#7eb00a', '#6f5553', '#c14089'
            ],
            textStyle: {
                fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                fontSize: 13
            },
            title: {
                text: "{{__('pages/dashboard/index.content.users.text')}}",
                subtext: "{{__('pages/dashboard/index.content.users.subText')}}",
                left: 'center',
                textStyle: {
                    fontSize: 17,
                    fontWeight: 500
                },
                subtextStyle: {
                    fontSize: 12
                }
            },
            tooltip: {
                trigger: 'item',
                backgroundColor: 'rgba(0,0,0,0.75)',
                padding: [10, 15],
                textStyle: {
                    fontSize: 13,
                    fontFamily: 'Roboto, sans-serif'
                },
                formatter: "{a} <br/>{b}: {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                top: 'center',
                left: 0,
                data: users.types,
                itemHeight: 8,
                itemWidth: 8
            },
            series: [{
                name: '{{__('pages/dashboard/index.content.users.text')}}',
                type: 'pie',
                radius: '70%',
                center: ['50%', '57.5%'],
                itemStyle: {
                    normal: {
                        borderWidth: 1,
                        borderColor: '#fff'
                    }
                },
                data: users.data
            }]
        });


        let packages_counters = {!! json_encode($packages_counters) !!};

        echarts.init(document.getElementById('packages_statistics')).setOption({
            tooltip: {},
            legend: {
                data: packages_counters['names'],
            },
            xAxis: {
                type: 'category',
                data: ["{{__('pages/dashboard/index.content.purchase_count')}}"]
            },
            yAxis: {
                type: 'value',
            },
            series: packages_counters['data'],
        });
    </script>
@endpush
