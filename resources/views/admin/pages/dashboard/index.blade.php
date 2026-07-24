@extends('admin.layouts.app')

@section('title', __('pages/dashboard/index.breadcrumb.title'))

@section('content')

    @canany(['advertisers.inquiry', 'customers.inquiry'])
        {{--Users Statistic--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.users_statistics.title')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>

            <div class="card-body collapse show">
                @can(['advertisers.inquiry'])
                    {{--Advertisers--}}
                    <div class="row justify-content-center">
                        <div class="col-sm-4">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <a href="{{route('admin.advertisers.index')}}"
                                   class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                    <i class="icon-plus3"></i>
                                </a>
                                <div>
                                    <div class="font-weight-bold">
                                        {{__('pages/dashboard/index.content.users_statistics.new_advertisers')}}
                                    </div>
                                    <span class="text-muted"> {{$advertisers_counters['new']}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <a href="{{route('admin.advertisers.index')}}"
                                   class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                    <i class="icon-users"></i>
                                </a>
                                <div>
                                    <div class="font-weight-bold">
                                        {{__('pages/dashboard/index.content.users_statistics.total_advertisers')}}
                                    </div>
                                    <span class="text-muted"> {{$advertisers_counters['total']}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <a href="{{route('admin.advertisers.index')}}"
                                   class="btn bg-transparent border-indigo-400 text-indigo-400 rounded-circle border-2 btn-icon mr-3">
                                    <i class="icon-users"></i>
                                </a>
                                <div>
                                    <div class="font-weight-bold">
                                        {{__('pages/dashboard/index.content.users_statistics.elite_advertisers')}}
                                    </div>
                                    <span class="text-muted">{{$advertisers_counters['elite']}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--/Advertisers--}}
                @endcan

                @can(['customers.inquiry'])
                    <hr/>
                    {{--Customers--}}
                    <div class="row justify-content-center">
                        <div class="col-sm-4">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <a href="{{route('admin.customers.index')}}"
                                   class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                    <i class="icon-plus3"></i>
                                </a>
                                <div>
                                    <div class="font-weight-bold">
                                        {{__('pages/dashboard/index.content.users_statistics.new_customers')}}
                                    </div>
                                    <span class="text-muted"> {{$customers_counters['new']}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <a href="{{route('admin.customers.index')}}"
                                   class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                    <i class="icon-users"></i>
                                </a>
                                <div>
                                    <div class="font-weight-bold">
                                        {{__('pages/dashboard/index.content.users_statistics.total_customers')}}
                                    </div>
                                    <span class="text-muted"> {{$customers_counters['total']}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <a href="{{route('admin.customers.index')}}"
                                   class="btn bg-transparent border-indigo-400 text-indigo-400 rounded-circle border-2 btn-icon mr-3">
                                    <i class="icon-users"></i>
                                </a>
                                <div>
                                    <div class="font-weight-bold">
                                        {{__('pages/dashboard/index.content.users_statistics.active_customers')}}
                                    </div>
                                    <span class="text-muted">{{$customers_counters['active']}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--/Customers--}}
                @endcan
                <br/>
            </div>
        </div>
        {{--/Users Statistics--}}
    @endcanany
    @canany(['statistics.requests'])
        {{--Requests Statistic--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.requests_statistics.title')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>

            <div class="card-body collapse show">
                {{--Requests--}}
                <div class="row justify-content-center">
                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.requests.contact-us.index')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-mailbox"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.requests_statistics.contact-us')}}
                                </div>
                                <span class="text-muted"> {{$requests_counters['contact-us']}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.requests.change-name.index')}}"
                               class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-mailbox"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.requests_statistics.username-change')}}
                                </div>
                                <span class="text-muted"> {{$requests_counters['username-change']}}</span>
                            </div>
                        </div>
                    </div>
                    {{--/Requests--}}<br/>
                </div>
            </div>
        </div>
        {{--/Requests Statistics--}}
    @endcanany
    @canany(['statistics.reports'])
        {{--Community Statistic--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.community_statistics.title')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>
            <div class="card-body collapse show">
                {{--Reports--}}
                <div class="row">
                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.posts.index')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag3"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.community_statistics.posts')}}
                                </div>
                                <span class="text-muted"> {{$community_counters['posts']}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.offers.index')}}"
                               class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag4"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.community_statistics.offers')}}
                                </div>
                                <span class="text-muted"> {{$community_counters['offers']}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.proposals.index')}}"
                               class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag7"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.community_statistics.proposals')}}
                                </div>
                                <span class="text-muted"> {{$community_counters['proposals']}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                    <hr/>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.comments.index')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag3"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.community_statistics.posts-comments')}}
                                </div>
                                <span class="text-muted"> {{$community_counters['posts_comments']}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.offers.comments.index')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag3"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.community_statistics.offers-comments')}}
                                </div>
                                <span class="text-muted"> {{$community_counters['offers_comments']}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                {{--/Reports--}}
            </div>
        </div>
        {{--/Community Statistics--}}
        {{--Reports Statistic--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.reports_statistics.title')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>
            <div class="card-body collapse show">
                {{--Reports--}}
                <div class="row">
                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.reports.posts')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag3"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.reports_statistics.posts')}}
                                </div>
                                <span class="text-muted"> {{$reports_counters['posts']}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.reports.offers')}}"
                               class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag4"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.reports_statistics.offers')}}
                                </div>
                                <span class="text-muted"> {{$reports_counters['offers']}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.reports.proposals')}}"
                               class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag7"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.reports_statistics.proposals')}}
                                </div>
                                <span class="text-muted"> {{$reports_counters['proposals']}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                    <hr/>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.reports.comments')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag3"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.reports_statistics.posts-comments')}}
                                </div>
                                <span class="text-muted"> {{$reports_counters['posts_comments']}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.community.offers.comments.reports')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-flag3"></i>
                            </a>
                            <div>
                                <div class="font-weight-bold">
                                    {{__('pages/dashboard/index.content.reports_statistics.offers-comments')}}
                                </div>
                                <span class="text-muted"> {{$reports_counters['offers_comments']}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                {{--/Reports--}}
            </div>
        </div>
        {{--/Reports Statistics--}}
    @endcanany
    @canany(['statistics.users'])
        {{--Users map--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.users_onMap.title')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>

            <div class="card-body collapse show">
                <div class="map-container map-world-users"></div>
            </div>
        </div>
        {{--/Users map--}}
    @endcanany
    @canany(['statistics.payments'])
        {{--Transactions Statistics--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.transactions_statistics.title')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>

            <div class="card-body collapse show">

                {{--Transactions--}}
                <div class="row">
                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.subscriptions.payments.index')}}"
                               class="btn bg-transparent border-teal text-teal rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-cash4"></i>
                            </a>
                            <div>
                                <div
                                    class="font-weight-semibold">{{__('pages/dashboard/index.content.transactions_statistics.new')}}</div>
                                <span class="text-muted"> {{$transactions_counters['new']}} {{__('pages/dashboard/index.content.transactions_statistics.SAR')}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.subscriptions.payments.index')}}"
                               class="btn bg-transparent border-warning-400 text-warning-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-cash4"></i>
                            </a>
                            <div>
                                <div
                                    class="font-weight-semibold">{{__('pages/dashboard/index.content.transactions_statistics.total')}}</div>
                                <span class="text-muted"> {{$transactions_counters['total']}} {{__('pages/dashboard/index.content.transactions_statistics.SAR')}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <a href="{{route('admin.subscriptions.payments.index')}}"
                               class="btn bg-transparent border-indigo-400 text-indigo-400 rounded-circle border-2 btn-icon mr-3">
                                <i class="icon-cash4"></i>
                            </a>
                            <div>
                                <div
                                    class="font-weight-semibold">{{__('pages/dashboard/index.content.transactions_statistics.ended')}}</div>
                                <span class="text-muted">{{$transactions_counters['ended']}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                {{--/Transactions--}}
                <br/>
                <div class="chart has-fixed-height" id="transactions_counter"></div>
            </div>
        </div>
        {{--/Transactions Statistics--}}
    @endcanany
    @canany(['statistics.packages'])
        {{--Packages Statistics--}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">{{__('pages/dashboard/index.content.packages_statistics')}}</h5>
                <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                    </div>
                </div>
            </div>

            <div class="card-body collapse show">
                <div class="chart has-fixed-height" id="packages_statistics"></div>
            </div>
        </div>
        {{--/Packages Statistics--}}
    @endcanany
    @canany(['statistics.proposals', 'statistics.users'])
        <div class="row">
            @can('statistics.proposals')
                {{--students devices--}}
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title">{{__('pages/dashboard/index.content.proposals_statistics.title')}}</h5>
                            <div class="header-elements">
                                <div class="list-icons">
                                    <a class="list-icons-item" data-action="collapse"></a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body collapse show">
                            <div class="chart-container">
                                <div class="chart has-fixed-height" id="proposals_statistics"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {{--/students devices--}}
            @endcan
            @can('statistics.users')
                {{--students devices--}}
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h5 class="card-title">{{__('pages/dashboard/index.content.users.title')}}</h5>
                            <div class="header-elements">
                                <div class="list-icons">
                                    <a class="list-icons-item" data-action="collapse"></a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body collapse show">
                            <div class="chart-container">
                                <div class="chart has-fixed-height" id="users_statistics"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {{--/students devices--}}
            @endcan
        </div>
    @endcanany
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

            /*Colors*/
            color: [
                '#2ec7c9', '#b6a2de', '#5ab1ef', '#ffb980', '#d87a80',
                '#8d98b3', '#e5cf0d', '#97b552', '#95706d', '#dc69aa',
                '#07a2a4', '#9a7fd1', '#588dd5', '#f5994e', '#c05050',
                '#59678c', '#c9ab00', '#7eb00a', '#6f5553', '#c14089'
            ],

            /*Global text styles*/
            textStyle: {
                fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                fontSize: 13
            },

            /*Add title*/
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

            /*Add tooltip*/
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

            /*Add legend*/
            legend: {
                orient: 'vertical',
                top: 'center',
                left: 0,
                data: proposals.types,
                itemHeight: 8,
                itemWidth: 8
            },

            /*Add series*/
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

            /*Colors*/
            color: [
                '#2ec7c9', '#b6a2de', '#5ab1ef', '#ffb980', '#d87a80',
                '#8d98b3', '#e5cf0d', '#97b552', '#95706d', '#dc69aa',
                '#07a2a4', '#9a7fd1', '#588dd5', '#f5994e', '#c05050',
                '#59678c', '#c9ab00', '#7eb00a', '#6f5553', '#c14089'
            ],

            /*Global text styles*/
            textStyle: {
                fontFamily: 'Roboto, Arial, Verdana, sans-serif',
                fontSize: 13
            },

            /*Add title*/
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

            /*Add tooltip*/
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

            /*Add legend*/
            legend: {
                orient: 'vertical',
                top: 'center',
                left: 0,
                data: users.types,
                itemHeight: 8,
                itemWidth: 8
            },

            /*Add series*/
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

        /*Sessions counter*/
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
