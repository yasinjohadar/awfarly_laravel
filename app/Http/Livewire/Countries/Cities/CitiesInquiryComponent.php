<?php

namespace App\Http\Livewire\Countries\Cities;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CitiesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.countries.cities.edit';
    public string $afterTableSlot2 = 'modals.countries.cities.add';
    public $model = City::class;
    public $governorate_id = null;
    public array $city = [];
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

        if (!$this->governorate_id) {
            $columns[] = Column::callback(['governorate_id'], function ($governorateId) {
                $governorate = Governorate::find($governorateId);
                if (!$governorate) {
                    return $governorateId;
                }

                return App::currentLocale() === 'ar' ? $governorate->name_ar : $governorate->name_en;
            })
                ->label(__('pages/countries/cities/index.content.datatable.governorate'))
                ->unsortable();
        }

        $columns[] = Column::name('name_en')
            ->label(__('pages/countries/cities/index.content.datatable.name_en'))
            ->filterable()
            ->searchable();
        $columns[] = Column::name('name_ar')
            ->label(__('pages/countries/cities/index.content.datatable.name_ar'))
            ->filterable()
            ->searchable();
        $columns[] = Column::callback(['id'], function ($id) {
            return view('admin.pages.countries.cities.table-actions', ['id' => $id]);
        })
            ->label(__('datatable.actions'))
            ->excludeFromExport()
            ->unsortable();

        return $columns;
    }

    public function builder()
    {
        $query = City::query()->orderBy('governorate_id')->orderBy('order');

        if ($this->governorate_id) {
            $query->where('governorate_id', $this->governorate_id);
        }

        return $query;
    }

    public function showDeleteModal()
    {
        $this->showDeleteModal = true;
    }

    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('cities.delete')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        DB::beginTransaction();
        try {
            $cities = City::whereIn('id', $this->selected)->get();
            parent::delete($this->selected);
            $this->selected = [];

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            AdminLogs::log('delete', 'cities', [
                'cities' => $cities,
            ], 'Delete: cities');

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
        $this->city = City::where('id', $id)->first()->toArray();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->city = [];
        $this->resetValidation();
    }

    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('cities.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        $this->validate([
            'city.name_en' => ['required'],
            'city.name_ar' => ['required'],
        ]);

        $data = $this->city;
        $data['name_en'] = Filter::RemoveHtml($this->city['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->city['name_ar']);
        unset($data['id']);

        DB::beginTransaction();
        try {
            $city = City::findOrFail($id);

            AdminLogs::log('edit', 'cities', [
                'old' => $city,
                'new' => $data,
            ], "Edit: city #$id");

            $city->update($data);
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
            'title' => __('pages/countries/cities/index.modal.delete.title'),
            'content' => __('pages/countries/cities/index.modal.delete.content'),
            'cancel' => __('pages/countries/cities/index.modal.delete.cancel'),
            'submit' => __('pages/countries/cities/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/countries/cities/index.modal.edit.title'),
            'cancel' => __('pages/countries/cities/index.modal.edit.cancel'),
            'submit' => __('pages/countries/cities/index.modal.edit.submit'),
        ];
    }

    public function showAddModal()
    {
        $this->city = [];
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->city = [];
        $this->resetValidation();
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('cities.add')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        $rules = [
            'city.name_en' => ['required'],
            'city.name_ar' => ['required'],
        ];

        if ($this->governorate_id) {
            $rules['governorate_id'] = ['required', 'exists:governorates,id'];
        } else {
            $rules['city.governorate_id'] = ['required', 'exists:governorates,id'];
        }

        $this->validate($rules);

        $governorateId = $this->governorate_id ?: ($this->city['governorate_id'] ?? null);

        if (!$governorateId) {
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        DB::beginTransaction();
        try {
            City::create([
                'governorate_id' => $governorateId,
                'name_en' => $this->city['name_en'],
                'name_ar' => $this->city['name_ar'],
            ]);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset(['city']);
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
