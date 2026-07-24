<?php

namespace App\Http\Controllers\Admins\Dashboard;

use App\Helpers\Countries;
use App\Helpers\Filter;
use App\Http\Controllers\Controller;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Offers\Offer;
use App\Models\Posts\Comments\PostComments;
use App\Models\Posts\Post;
use App\Models\Proposals\Proposal;
use App\Models\Reports\Report;
use App\Models\Requests\ContactForms;
use App\Models\Requests\UsernameRequests;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\App;

class AdminDashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('admin.pages.dashboard.index', [
            'advertisers_counters' => $this->advertisersCounters(),
            'customers_counters' => $this->customersCounters(),
            'users_by_countries' => $this->getUsersByCountry(),
            'transactions_counters' => $this->getTransactionsCounter(),
            'proposals' => $this->getProposalsCounters(),
            'users' => $this->getUsersCounters(),
            'packages_counters' => $this->getPackagesCounters(),
            'requests_counters' => $this->getRequestsCounters(),
            'reports_counters' => $this->getReportsCounters(),
            'community_counters' => $this->getCommunityCounters(),
        ]);
    }

    /**
     * @return array|string[]
     */
    public function advertisersCounters(): array
    {
        //get new advertisers
        $counter['new'] = AdvertiserUser::whereBetween('created_at', [
            Carbon::now()->startOfDay(),
            Carbon::now()->endOfDay(),
        ])
            ->count();
        $counter['new'] = Filter::RestyleNumbers($counter['new']);

        //get total advertisers
        $counter['total'] = AdvertiserUser::count();
        $counter['total'] = Filter::RestyleNumbers($counter['total']);

        //get elite advertisers
        $counter['elite'] = AdvertiserUser::where('is_elite', true)
            ->count();
        $counter['elite'] = Filter::RestyleNumbers($counter['elite']);

        //Set counter style
        return array_map(function ($data) {
            return Filter::RestyleNumbers($data, true);
        }, $counter);
    }

    /**
     * @return array|string[]
     */
    public function customersCounters(): array
    {
        //get new customers
        $counter['new'] = CustomerUser::whereBetween('created_at', [
            Carbon::now()->startOfDay(),
            Carbon::now()->endOfDay(),
        ])
            ->count();
        $counter['new'] = Filter::RestyleNumbers($counter['new']);

        //get total customers
        $counter['total'] = CustomerUser::count();
        $counter['total'] = Filter::RestyleNumbers($counter['total']);

        //get active customers
        $counter['active'] = CustomerUser::where('status', 'active')
            ->count();
        $counter['active'] = Filter::RestyleNumbers($counter['active']);

        //Set counter style
        return array_map(function ($data) {
            return Filter::RestyleNumbers($data, true);
        }, $counter);
    }

    /**
     * Get customers by the country
     * @return array
     */
    public function getUsersByCountry(): array
    {
        //return users counters
        return CustomerUser::select(['country_code'])
            ->unionAll(AdvertiserUser::select(['country_code']))
            ->get()
            ->groupBy('country_code')
            ->map(function ($data) {
                $key = $data->first()
                    ->country_code;
                //Get coords
                $coords = Countries::GetCountryCoordsByCodes($key);
                $coords = $coords['coords'] ?? null;
                //Get coords
                $country = (Countries::GetNameFromCode($key) ?? null);
                //Set count
                $count = $data->count();
                return [
                    'code' => $key,
                    'name' => "$country ($count)",
                    'latLng' => $coords,
                ];
            })
            ->toArray();
    }

    /**
     * Get Transactions counter
     * @return array
     */
    public function getTransactionsCounter(): array
    {
        //Set months
        $months_keys = [
            'jan',
            'feb',
            'mar',
            'apr',
            'may',
            'jun',
            'jul',
            'aug',
            'sep',
            'oct',
            'nov',
            'dec',
        ];

        //Set current month number
        $current_month = Carbon::now()->month;

        //Set months numbers
        $months_keys_ordered_nums = [];

        //Create counters
        for ($index = 1; $index <= 12; $index++) {
            //Set months orders
            $month = ($index == 1) ? $current_month : last($months_keys_ordered_nums) - 1;
            $months_keys_ordered_nums[] = (($month > 0) ? $month : 12 - $month);
            $month_name = $months_keys[(($month > 0) ? $month : 12 - $month) - 1];

            //amount
            $counter['months'][$month_name]['amount'] = 0;

            $advertisers_packlages = AdvertiserPackages::query()
                ->join('packages', function ($q) {
                    $q->on('packages.id', 'advertiser_packages.package_id')
                        ->where('packages.is_visible', true);
                })
                ->whereYear('advertiser_packages.created_at', Carbon::now()->subMonths($index - 1)->year)
                ->whereMonth('advertiser_packages.created_at', Carbon::now()->subMonths($index - 1)->month)
                ->get();

            foreach ($advertisers_packlages as $advertiser_package) {
                $package = $advertiser_package->package;
                $counter['months'][$month_name]['amount'] += $package ? $package->price * $advertiser_package->purchase_count : 0.00;
            }
            /*$counter['months'][$month_name]['amount'] = Package::join('advertiser_packages', function ($q) use ($index) {
                    return $q->on('advertiser_packages.package_id', 'packages.id')
                        ->whereYear('advertiser_packages.created_at', Carbon::now()->subMonths($index - 1)->year)
                        ->whereMonth('advertiser_packages.created_at', Carbon::now()->subMonths($index - 1)->month);

                })
                    ->where('packages.is_visible', true)
                    ->sum('price') ?? 0.00;*/
            $counter['months'][$month_name]['amount'] = Filter::RestyleNumbers($counter['months'][$month_name]['amount']);

            //count
            $counter['months'][$month_name]['count'] = AdvertiserPackages::whereYear('created_at', Carbon::now()->subMonths($index - 1)->year)
                ->whereMonth('created_at', Carbon::now()->subMonths($index - 1)->month)
                ->whereHas('package', function ($q) {
                    $q->where('is_visible', true);
                })
                ->count();
            $counter['months'][$month_name]['count'] = Filter::RestyleNumbers($counter['months'][$month_name]['count']);

            //advertisers
            $counter['months'][$month_name]['advertisers'] = AdvertiserPackages::distinct('advertiser_id')
                ->whereHas('package', function ($q) {
                    $q->where('is_visible', true);
                })
                ->whereYear('created_at', Carbon::now()->subMonths($index - 1)->year)
                ->whereMonth('created_at', Carbon::now()->subMonths($index - 1)->month)
                ->count();

            $counter['months'][$month_name]['advertisers'] = Filter::RestyleNumbers($counter['months'][$month_name]['advertisers']);
        }

        $new_packages = AdvertiserPackages::query()
            ->join('packages', function ($q) {
                $q->on('packages.id', 'advertiser_packages.package_id')
                    ->where('packages.is_visible', true);
            })
            ->whereBetween('advertiser_packages.created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->get();

        $new = 0.00;
        foreach ($new_packages as $new_package) {
            $package = $new_package->package;
            $new += $package ? $package->price * $new_package->purchase_count : 0.00;
        }
        /*$new = Package::query()
                ->join('advertiser_packages', function ($q) use ($index) {
                    return $q->on('advertiser_packages.package_id', 'packages.id')
                        ->whereBetween('advertiser_packages.created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek(),
                        ]);
                })
                ->where('packages.is_visible', true)
                ->sum('price') ?? 0.00;*/

        $new = Filter::RestyleNumbers(number_format($new, 2, '.', ','));

        $total = Package::join('advertiser_packages', 'advertiser_packages.package_id', 'packages.id')
                ->where('packages.is_visible', true)
                ->sum('price') ?? 0.00;

        $total = Filter::RestyleNumbers($total);

        $ended = AdvertiserPackages::where('is_ended', true)
            ->whereHas('package', function ($q) {
                $q->where('is_visible', true);
            })
            ->count();
        $ended = Filter::RestyleNumbers($ended);

        //Reverse months
        return [
            'months' => array_reverse($counter['months']),
            'new' => $new,
            'total' => $total,
            'ended' => $ended,
        ];
    }

    /**
     * Get proposals counters
     * @return array|array[]
     */
    public function getProposalsCounters(): array
    {
        //Get students
        $unanswered = Proposal::whereNull('answer')
            ->orWhere('answer', '')
            ->whereBetween('updated_at', [
                Carbon::now()->subMonth(),
                Carbon::now(),
            ])
            ->count();
        $unanswered = Filter::RestyleNumbers($unanswered);

        $answered = Proposal::whereNotNull('answer')
            ->where('answer', '!=', '')
            ->whereBetween('updated_at', [
                Carbon::now()->subMonth(),
                Carbon::now(),
            ])
            ->count();
        $answered = Filter::RestyleNumbers($answered);

        return [
            'types' => [
                __('pages/dashboard/index.content.proposals_statistics.types.answered'),
                __('pages/dashboard/index.content.proposals_statistics.types.unanswered')
            ],
            'data' => [
                [
                    'name' => __('pages/dashboard/index.content.proposals_statistics.types.answered'),
                    'value' => $answered,
                ],
                [
                    'name' => __('pages/dashboard/index.content.proposals_statistics.types.unanswered'),
                    'value' => $unanswered,
                ],
            ]
        ];
    }

    /**
     * Get users counters
     * @return array|array[]
     */
    public function getUsersCounters(): array
    {
        //Get users
        $advertisers = AdvertiserUser::where('status', 'active')
            ->count();
        $advertisers = Filter::RestyleNumbers($advertisers);

        $customers = CustomerUser::where('status', 'active')
            ->count();
        $customers = Filter::RestyleNumbers($customers);

        return [
            'types' => [
                __('pages/dashboard/index.content.users.types.advertisers'),
                __('pages/dashboard/index.content.users.types.customers')
            ],
            'data' => [
                [
                    'name' => __('pages/dashboard/index.content.users.types.advertisers'),
                    'value' => $advertisers,
                ],
                [
                    'name' => __('pages/dashboard/index.content.users.types.customers'),
                    'value' => $customers,
                ],
            ]
        ];
    }

    /**
     * Get Packages counters
     * @return array
     */
    public function getPackagesCounters(): array
    {
        $packages = Package::where('is_active', true)
            ->where('is_visible', true)
            ->limit(10)
            ->get()
            ->sortBy(function ($query) {
                return $query->advertisers->count();
            }, SORT_REGULAR, true)
            ->map(function ($query) {
                $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';
                return [
                    'name' => $query->{$name_column},
                    'type' => 'bar',
                    'smooth' => true,
                    'data' => [$query->advertisers->count()],
                    'showBackground' => true,
                    'backgroundStyle' => [
                        'color' => 'rgba(180, 180, 180, 0.2)'
                    ],
                ];
            })
            ->toArray();
        $packages = array_values($packages);
        $names = [];
        foreach ($packages as $package) {
            $names[] = $package['name'];
        }

        return [
            'names' => $names,
            'data' => $packages,
        ];
    }

    /**
     * Requests counters
     * @return array
     */
    public function getRequestsCounters(): array
    {
        $contact_us = ContactForms::where('status', 'unread')
            ->count();
        $contact_us = Filter::RestyleNumbers($contact_us);

        $change_username = UsernameRequests::where('status', 'pending')
            ->count();
        $change_username = Filter::RestyleNumbers($change_username);

        return [
            'contact-us' => $contact_us,
            'username-change' => $change_username,
        ];
    }

    /**
     * reports_counter
     * @return array
     */
    public function getReportsCounters(): array
    {
        $reported_posts = Report::where('reported_type', Post::class)
            ->where('status', 'pending')
            ->count();
        $reported_posts = Filter::RestyleNumbers($reported_posts);

        $reported_offers = Report::where('reported_type', Offer::class)
            ->where('status', 'pending')
            ->count();
        $reported_offers = Filter::RestyleNumbers($reported_offers);

        $reported_proposals = Report::where('reported_type', Proposal::class)
            ->where('status', 'pending')
            ->count();
        $reported_proposals = Filter::RestyleNumbers($reported_proposals);

        $reported_posts_comments = Report::where('reported_type', PostComments::class)
            ->where('status', 'pending')
            ->count();
        $reported_posts_comments = Filter::RestyleNumbers($reported_posts_comments);

        $reported_offers_comments = Report::where('reported_type', OffersComments::class)
            ->where('status', 'pending')
            ->count();
        $reported_offers_comments = Filter::RestyleNumbers($reported_offers_comments);

        return [
            'posts' => $reported_posts,
            'offers' => $reported_offers,
            'proposals' => $reported_proposals,
            'posts_comments' => $reported_posts_comments,
            'offers_comments' => $reported_offers_comments,
        ];
    }

    /**
     * Community counters
     * @return array
     */
    public function getCommunityCounters(): array
    {
        $posts = Post::count();
        $posts = Filter::RestyleNumbers($posts);

        $posts_comments = PostComments::count();
        $posts_comments = Filter::RestyleNumbers($posts_comments);

        $offers = Offer::count();
        $offers = Filter::RestyleNumbers($offers);

        $offers_comments = OffersComments::count();
        $offers_comments = Filter::RestyleNumbers($offers_comments);

        $proposals = Proposal::count();
        $proposals = Filter::RestyleNumbers($proposals);

        return [
            'posts' => $posts,
            'posts_comments' => $posts_comments,
            'offers' => $offers,
            'offers_comments' => $offers_comments,
            'proposals' => $proposals,
        ];
    }

}
