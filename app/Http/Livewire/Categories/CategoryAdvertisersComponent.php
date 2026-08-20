<?php

namespace App\Http\Livewire\Categories;

use App\Models\Categories\Category;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;

class CategoryAdvertisersComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.category-advertisers-filters';
    public $model = AdvertiserUser::class;
    public bool $has_delete = false;

    /**
     * the category the details page is opened for
     * @var int|null
     */
    public ?int $category_id = null;

    /**
     * which categories to pull advertisers from: all|direct|subs
     * @var string
     */
    public string $scope = 'all';

    /**
     * narrow the results down to a single sub category
     * @var string|null
     */
    public ?string $sub_category_id = null;

    /**
     * account status filter
     * @var string|null
     */
    public ?string $status_filter = null;

    /**
     * elite filter: null|1|0
     * @var string|null
     */
    public ?string $elite_filter = null;

    /**
     * package filter: null|none|{package_id}
     * @var string|null
     */
    public ?string $package_filter = null;

    /**
     * soft deleted advertisers: without|with|only
     * @var string
     */
    public string $trashed_filter = 'without';

    /**
     * sub categories of the viewed category, used by the filters bar
     * @var Collection
     */
    public Collection $sub_categories;

    /**
     * active packages, used by the filters bar
     * @var Collection
     */
    public Collection $packages;

    /**
     * localized name column of the translatable tables
     * @var string
     */
    private string $name_column = '';

    /**
     * memoized target category ids, the builder and the raw sub queries ask
     * for them several times per request
     * @var array|null
     */
    private ?array $target_ids = null;

    /**
     * CategoryAdvertisersComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //get admin language
        $this->getAdminLanguage();

        $this->sub_categories = new Collection();
        $this->packages = new Collection();

        parent::__construct($id);
    }

    /**
     * @param null $model
     * @param array $include
     * @param array $exclude
     * @param array $hide
     * @param array $dates
     * @param array $times
     * @param array $searchable
     * @param null $sort
     * @param null $hideHeader
     * @param null $hidePagination
     * @param int $perPage
     * @param false $exportable
     * @param false $hideable
     * @param false $beforeTableSlot
     * @param false $afterTableSlot
     * @param array $params
     */
    public function mount($model = null, $include = [], $exclude = [], $hide = [], $dates = [], $times = [], $searchable = [], $sort = null, $hideHeader = null, $hidePagination = null, $perPage = 10, $exportable = false, $hideable = false, $beforeTableSlot = false, $afterTableSlot = false, $params = [])
    {
        //get the sub categories of the viewed category
        $this->sub_categories = Category::where('parent_category_id', $this->category_id)
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->{$this->name_column} ?: $category->name_en,
                ];
            });

        //get the active packages
        $this->packages = Package::where('is_active', true)
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->{$this->name_column} ?: $package->name_en,
                ];
            });

        parent::mount($model, $include, $exclude, $hide, $dates, $times, $searchable, $sort, $hideHeader, $hidePagination, $perPage, $exportable, $hideable, $beforeTableSlot, $afterTableSlot, $params);
    }

    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        $base = 'pages/categories/show.content.advertisers.datatable';

        return [
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::callback(['image', 'name'], function ($image, $name) {
                return '<div class="text-center"><a href="' . route('users.profile.image', $image) . '" target="_blank">
                            <img class="rounded-circle" width="38" height="38" src="' . route('users.profile.image', $image) . '" alt="' . e($name) . '"/>
                        </a></div>';
            })
                ->label(__("$base.image"))
                ->excludeFromExport()
                ->unsortable(),
            Column::name('name')
                ->label(__("$base.name"))
                ->filterable()
                ->searchable(),
            Column::callback('username', function ($username) {
                return $username ?: '-';
            })
                ->label(__("$base.username"))
                ->filterable()
                ->searchable(),
            Column::name("advertisers_business_types.$this->name_column")
                ->label(__("$base.business_type"))
                ->filterable($this->all_business_types)
                ->searchable(),
            Column::raw($this->matchedCategoriesSubQuery() . ' AS matched_categories')
                ->label(__("$base.categories"))
                ->filterable()
                ->searchable(),
            Column::callback('mobile', function ($mobile) {
                return $mobile ? "<a dir='ltr' class='ltr' href='tel:$mobile'>$mobile</a>" : '-';
            })
                ->label(__("$base.mobile"))
                ->filterable()
                ->searchable(),
            Column::callback('email', function ($email) {
                return $email ? "<a dir='ltr' class='ltr' href='mailto:$email'>$email</a>" : '-';
            })
                ->label(__("$base.email"))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('contact_number', function ($contact_number) {
                return $contact_number ? "<span dir='ltr' class='ltr'>$contact_number</span>" : '-';
            })
                ->label(__("$base.contact_number"))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('whatsapp_number', function ($whatsapp_number) {
                return $whatsapp_number ? "<span dir='ltr' class='ltr'>$whatsapp_number</span>" : '-';
            })
                ->label(__("$base.whatsapp_number"))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name("governorates.$this->name_column")
                ->label(__("$base.governorate"))
                ->filterable($this->all_governorates)
                ->searchable(),
            Column::name("cities.$this->name_column")
                ->label(__("$base.city"))
                ->filterable($this->all_cities)
                ->searchable(),
            Column::callback('status', function ($status) use ($base) {
                $classes = [
                    'active' => 'success',
                    'inactive' => 'warning',
                    'banned' => 'dark',
                    'closed' => 'danger',
                ];
                $class = $classes[$status] ?? 'secondary';
                $label = __("$base.status_type.$status");

                return '<span class="badge badge-' . $class . '">' . e($label) . '</span>';
            })
                ->label(__("$base.status"))
                ->filterable($this->all_statuses),
            BooleanColumn::name('is_elite')
                ->label(__("$base.is_elite"))
                ->filterable(),
            NumberColumn::callback('rate', function ($rate) {
                return $rate ?: '-';
            })
                ->label(__("$base.rate"))
                ->filterable()
                ->alignCenter(),
            Column::callback('id', function ($id) use ($base) {
                $package = AdvertiserUser::withTrashed()
                    ->where('id', $id)
                    ->first()
                    ->packages()
                    ->where('is_current', true)
                    ->where('is_active', true)
                    ->where('is_ended', false)
                    ->where('ends_at', '>', now())
                    ->with('package')
                    ->first();

                if (!$package || !$package->package) {
                    return '<span class="badge badge-secondary">' . e(__("$base.no_package")) . '</span>';
                }

                $name = $package->package->{$this->name_column} ?: $package->package->name_en;

                return '<span class="badge badge-primary">' . e($name) . '</span>';
            })
                ->label(__("$base.package"))
                ->unsortable(),
            NumberColumn::raw($this->categoryCountSubQuery('offers', 'advertiser_id') . ' AS category_offers_count')
                ->label(__("$base.offers_count"))
                ->filterable()
                ->alignCenter(),
            NumberColumn::raw($this->categoryCountSubQuery('posts', 'user_id') . ' AS category_posts_count')
                ->label(__("$base.posts_count"))
                ->filterable()
                ->alignCenter(),
            DateColumn::raw($this->joinedAtSubQuery() . ' AS category_joined_at')
                ->format('Y-m-d h:i A')
                ->label(__("$base.joined_at")),
            DateColumn::name('created_at')
                ->format('Y-m-d h:i A')
                ->label(__("$base.created_at"))
                ->hide(),
            Column::callback(['id', 'deleted_at'], function ($id, $deleted_at) {
                return view('admin.pages.categories.advertisers-table-actions', [
                    'id' => $id,
                    'deleted_at' => $deleted_at,
                ]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return mixed
     */
    public function builder()
    {
        $category_ids = $this->targetCategoryIds();

        return AdvertiserUser::query()
            ->when($this->trashed_filter === 'with', function ($query) {
                $query->withTrashed();
            })
            ->when($this->trashed_filter === 'only', function ($query) {
                $query->onlyTrashed();
            })
            ->leftJoin('advertisers_business_types', 'advertisers_business_types.id', 'advertisers_users.business_type')
            ->leftJoin('governorates', 'governorates.id', 'advertisers_users.governorate_id')
            ->leftJoin('cities', 'cities.id', 'advertisers_users.city_id')
            ->whereIn('advertisers_users.id', function ($query) use ($category_ids) {
                $query->select('advertiser_id')
                    ->from('advertiser_categories')
                    ->whereIn('category_id', $category_ids);
            })
            ->when($this->status_filter, function ($query) {
                $query->where('advertisers_users.status', $this->status_filter);
            })
            ->when($this->elite_filter !== null && $this->elite_filter !== '', function ($query) {
                $query->where('advertisers_users.is_elite', (int)$this->elite_filter);
            })
            ->when($this->package_filter === 'none', function ($query) {
                $query->whereDoesntHave('packages', function ($packages) {
                    $this->currentPackageQuery($packages);
                });
            })
            ->when($this->package_filter && $this->package_filter !== 'none', function ($query) {
                $query->whereHas('packages', function ($packages) {
                    $this->currentPackageQuery($packages)
                        ->where('package_id', $this->package_filter);
                });
            });
    }

    /**
     * constrain a packages query to the currently running package
     * @param $query
     * @return mixed
     */
    private function currentPackageQuery($query)
    {
        return $query->where('is_current', true)
            ->where('is_active', true)
            ->where('is_ended', false)
            ->where('ends_at', '>', now());
    }

    /**
     * all the categories the listed advertisers may be affiliated to,
     * honouring the scope and the sub category filters
     * @return array
     */
    public function targetCategoryIds(): array
    {
        if ($this->target_ids !== null) {
            return $this->target_ids;
        }

        if (!$this->category_id) {
            return $this->target_ids = [];
        }

        //a single sub category is chosen, only it and its own descendants count
        if ($this->sub_category_id) {
            $sub_category_id = (int)$this->sub_category_id;

            //make sure the chosen sub category really belongs to the viewed one
            if (Category::where('id', $sub_category_id)->where('parent_category_id', $this->category_id)->exists()) {
                return $this->target_ids = array_merge([$sub_category_id], $this->descendantIds($sub_category_id));
            }

            $this->sub_category_id = null;
        }

        $descendants = $this->descendantIds($this->category_id);

        switch ($this->scope) {
            case 'direct':
                return $this->target_ids = [$this->category_id];
            case 'subs':
                return $this->target_ids = $descendants;
            default:
                return $this->target_ids = array_merge([$this->category_id], $descendants);
        }
    }

    /**
     * get all the nested categories of a category
     * @param int $category_id
     * @return array
     */
    private function descendantIds(int $category_id): array
    {
        $ids = [];
        $parents = [$category_id];

        //walk the tree level by level, the tree is shallow so a few rounds are enough
        while (!empty($parents)) {
            $children = Category::whereIn('parent_category_id', $parents)
                ->whereNotIn('id', array_merge($ids, [$category_id]))
                ->pluck('id')
                ->toArray();

            if (empty($children)) {
                break;
            }

            $ids = array_merge($ids, $children);
            $parents = $children;
        }

        return $ids;
    }

    /**
     * a sub query listing the categories, within the viewed tree,
     * the advertiser of the row is affiliated to
     * @return string
     */
    private function matchedCategoriesSubQuery(): string
    {
        $ids = $this->categoryIdsForSql();

        return "(SELECT GROUP_CONCAT(DISTINCT categories.$this->name_column ORDER BY categories.$this->name_column SEPARATOR ', ')
                 FROM advertiser_categories
                 INNER JOIN categories ON categories.id = advertiser_categories.category_id
                 WHERE advertiser_categories.advertiser_id = advertisers_users.id
                 AND advertiser_categories.category_id IN ($ids))";
    }

    /**
     * a sub query counting the rows of a table, belonging to the advertiser of
     * the row, that are published under the viewed categories
     * @param string $table
     * @param string $owner_column
     * @return string
     */
    private function categoryCountSubQuery(string $table, string $owner_column): string
    {
        $ids = $this->categoryIdsForSql();

        $query = "(SELECT COUNT(*) FROM $table
                   WHERE $table.$owner_column = advertisers_users.id
                   AND $table.category_id IN ($ids)
                   AND $table.deleted_at IS NULL";

        //posts are polymorphic, make sure only the advertiser ones are counted
        if ($table === 'posts') {
            $user_type = str_replace('\\', '\\\\', AdvertiserUser::class);
            $query .= " AND posts.user_type = '$user_type'";
        }

        return "$query)";
    }

    /**
     * a sub query returning the date the advertiser of the row got affiliated
     * to the viewed categories
     * @return string
     */
    private function joinedAtSubQuery(): string
    {
        $ids = $this->categoryIdsForSql();

        return "(SELECT MIN(advertiser_categories.created_at)
                 FROM advertiser_categories
                 WHERE advertiser_categories.advertiser_id = advertisers_users.id
                 AND advertiser_categories.category_id IN ($ids))";
    }

    /**
     * the target category ids, safe to inline inside a raw sub query
     * @return string
     */
    private function categoryIdsForSql(): string
    {
        $ids = array_map('intval', $this->targetCategoryIds());

        return empty($ids) ? '0' : implode(',', $ids);
    }

    /**
     * get admin language
     */
    public function getAdminLanguage()
    {
        if (Auth::guard('admin')->user()->language_code === 'ar') {
            $this->name_column = 'name_ar';
        } else {
            $this->name_column = 'name_en';
        }
    }

    /**
     * @return mixed
     */
    public function getAllBusinessTypesProperty()
    {
        return AdvertiserBusinessType::pluck($this->name_column);
    }

    /**
     * @return mixed
     */
    public function getAllGovernoratesProperty()
    {
        return Governorate::pluck($this->name_column);
    }

    /**
     * @return mixed
     */
    public function getAllCitiesProperty()
    {
        return City::pluck($this->name_column);
    }

    /**
     * @return array
     */
    public function getAllStatusesProperty(): array
    {
        $base = 'pages/categories/show.content.advertisers.datatable.status_type';

        return [
            'active' => __("$base.active"),
            'inactive' => __("$base.inactive"),
            'banned' => __("$base.banned"),
            'closed' => __("$base.closed"),
        ];
    }

    /**
     * reset the pagination whenever one of the custom filters changes
     * @param string $field
     */
    public function updated($field)
    {
        if (in_array($field, ['scope', 'sub_category_id', 'status_filter', 'elite_filter', 'package_filter', 'trashed_filter'], true)) {
            //picking a single sub category makes the scope meaningless
            if ($field === 'sub_category_id' && $this->sub_category_id) {
                $this->scope = 'all';
            }

            $this->target_ids = null;
            $this->resetPage();
            $this->forgetComputed();
        }
    }

    /**
     * empty every filter of the table
     */
    public function resetFilters()
    {
        $this->scope = 'all';
        $this->sub_category_id = null;
        $this->status_filter = null;
        $this->elite_filter = null;
        $this->package_filter = null;
        $this->trashed_filter = 'without';
        $this->search = null;
        $this->activeSelectFilters = [];
        $this->activeBooleanFilters = [];
        $this->activeTextFilters = [];
        $this->activeNumberFilters = [];
        $this->activeDateFilters = [];
        $this->activeTimeFilters = [];

        $this->target_ids = null;
        $this->resetPage();
        $this->forgetComputed();
    }
}
