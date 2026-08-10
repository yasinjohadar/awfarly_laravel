<?php

namespace App\Http\Livewire\Countries\Governorates;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class GovernoratesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.countries.governorates.edit';
    public string $afterTableSlot2 = 'modals.countries.governorates.add';
    public $model = Governorate::class;
    public $country_id = null;
    public array $governorate = [];
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showAddModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public bool $has_delete = true;

    protected $listeners = [
        'showAddModal',
    ];

    public function __construct($id = null)
    {
        $this->setModalTexts();
        parent::__construct($id);
    }

    public function columns(): array
    {
        $columns = [
            Column::checkbox(),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
        ];

        if (!$this->country_id) {
            $columns[] = Column::callback(['country_code'], function ($countryCode) {
                $country = Country::where('code', $countryCode)->first();
                if (!$country) {
                    return $countryCode;
                }

                return App::currentLocale() === 'ar' ? $country->name_ar : $country->name_en;
            })
                ->label(__('pages/countries/governorates/index.content.datatable.country'))
                ->unsortable();
        }

        $columns[] = Column::name('name_en')
            ->label(__('pages/countries/governorates/index.content.datatable.name_en'))
            ->filterable()
            ->searchable();
        $columns[] = Column::name('name_ar')
            ->label(__('pages/countries/governorates/index.content.datatable.name_ar'))
            ->filterable()
            ->searchable();
        $columns[] = Column::callback(['id'], function ($id) {
            return view('admin.pages.countries.governorates.table-actions', ['id' => $id]);
        })
            ->label(__('datatable.actions'))
            ->excludeFromExport()
            ->unsortable();

        return $columns;
    }

    public function builder()
    {
        $query = Governorate::query()->orderBy('country_code')->orderBy('order');

        if ($this->country_id) {
            $country = Country::find($this->country_id);
            if ($country) {
                $query->where('country_code', $country->code);
            }
        }

        return $query;
    }

    /**
     * show delete modal for selected rows, or a single governorate by id
     * @param int|null $id
     */
    public function showDeleteModal($id = null)
    {
        if ($id !== null) {
            $this->selected = [(string) $id];
        }

        if (empty($this->selected)) {
            return;
        }

        $this->setDeleteModalTextsForSelection();
        $this->showDeleteModal = true;
    }

    /**
     * Set delete modal title/content based on selection count and governorate name
     */
    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/countries/governorates/index.modal.delete';

        if (count($this->selected) === 1) {
            $governorate = Governorate::find($this->selected[0]);
            $name = '';
            if ($governorate) {
                $name = App::currentLocale() === 'ar'
                    ? ($governorate->name_ar ?: $governorate->name_en)
                    : ($governorate->name_en ?: $governorate->name_ar);
            }

            $this->deleteModalTexts = [
                'title' => __("$base.title"),
                'content' => __("$base.content", ['name' => $name]),
                'cancel' => __("$base.cancel"),
                'submit' => __("$base.submit"),
            ];

            return;
        }

        $this->deleteModalTexts = [
            'title' => __("$base.title_multiple"),
            'content' => __("$base.content_multiple"),
            'cancel' => __("$base.cancel"),
            'submit' => __("$base.submit"),
        ];
    }

    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('governorates.delete')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        if (empty($this->selected)) {
            $this->showDeleteModal = false;
            return null;
        }

        DB::beginTransaction();
        try {
            $governorates = Governorate::whereIn('id', $this->selected)
                ->withCount('cities')
                ->get();

            //block delete if a governorate still has cities or referenced users/posts
            $inUse = $governorates->first(function ($governorate) {
                return $governorate->cities_count > 0;
            });

            if (!$inUse) {
                $ids = $governorates->pluck('id');
                $hasUsage = AdvertiserUser::whereIn('governorate_id', $ids)->exists()
                    || CustomerUser::whereIn('governorate_id', $ids)->exists()
                    || Post::whereIn('governorate_id', $ids)->exists();

                if ($hasUsage) {
                    $inUse = true;
                }
            }

            if ($inUse) {
                DB::rollBack();
                $this->alert('error', __('pages/countries/governorates/index.modal.delete.in_use'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
                $this->showDeleteModal = false;
                return null;
            }

            parent::delete($this->selected);
            $this->selected = [];

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            AdminLogs::log('delete', 'governorates', [
                'governorates' => $governorates,
            ], 'Delete: governorates');

            $this->showDeleteModal = false;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        DB::commit();
    }

    public function showEditModal($id)
    {
        $this->governorate = Governorate::where('id', $id)->first()->toArray();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->governorate = [];
        $this->resetValidation();
    }

    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('governorates.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        $this->validate([
            'governorate.country_code' => ['required', 'exists:countries,code'],
            'governorate.name_en' => ['required'],
            'governorate.name_ar' => ['required'],
        ]);

        $data = $this->governorate;
        $data['name_en'] = Filter::RemoveHtml($this->governorate['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->governorate['name_ar']);
        unset($data['id']);

        DB::beginTransaction();
        try {
            $governorate = Governorate::findOrFail($id);

            AdminLogs::log('edit', 'governorates', [
                'old' => $governorate,
                'new' => $data,
            ], "Edit: governorate #$id");

            $governorate->update($data);
            $this->closeEditModal();
            $this->resetValidation();

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        DB::commit();
    }

    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/countries/governorates/index.modal.delete.title'),
            'content' => __('pages/countries/governorates/index.modal.delete.content'),
            'cancel' => __('pages/countries/governorates/index.modal.delete.cancel'),
            'submit' => __('pages/countries/governorates/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/countries/governorates/index.modal.edit.title'),
            'cancel' => __('pages/countries/governorates/index.modal.edit.cancel'),
            'submit' => __('pages/countries/governorates/index.modal.edit.submit'),
        ];
    }

    public function showAddModal()
    {
        $this->governorate = [];
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->governorate = [];
        $this->resetValidation();
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('governorates.add')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        $rules = [
            'governorate.name_en' => ['required'],
            'governorate.name_ar' => ['required'],
        ];

        if ($this->country_id) {
            $rules['country_id'] = ['required', 'exists:countries,id'];
        } else {
            $rules['governorate.country_code'] = ['required', 'exists:countries,code'];
        }

        $this->validate($rules);

        $countryCode = $this->country_id
            ? optional(Country::find($this->country_id))->code
            : ($this->governorate['country_code'] ?? null);

        if (!$countryCode) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        DB::beginTransaction();
        try {
            Governorate::create([
                'country_code' => $countryCode,
                'name_en' => $this->governorate['name_en'],
                'name_ar' => $this->governorate['name_ar'],
            ]);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset(['governorate']);
            $this->closeAddModal();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }

        DB::commit();
    }
}
