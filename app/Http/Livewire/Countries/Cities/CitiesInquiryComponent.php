<?php

namespace App\Http\Livewire\Countries\Cities;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
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
    public int $country_id;
    public array $city = [];
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showAddModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public bool $has_delete = true;

    protected $listeners = [
        'showAddModal'
    ];

    /**
     * citiesInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //set modal texts
        $this->setModalTexts();

        parent::__construct($id);
    }

    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::checkbox(),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::name("name_en")
                ->label(__('pages/countries/cities/index.content.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name("name_ar")
                ->label(__('pages/countries/cities/index.content.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.countries.cities.table-actions', ['id' => $id]);
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
        $country = Country::find($this->country_id);

        return City::where('country_code', $country->code)
            ->orderBy('order');
    }

    /**
     * show delete modal
     */
    public function showDeleteModal()
    {
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('cities.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get cities
            $cities = City::whereIn('id', $this->selected)
                ->get();

            //delete data
            parent::delete($this->selected);

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'cities', [
                'cities' => $cities
            ], "Delete: cities");

            //close modal
            $this->showDeleteModal = false;

        } catch (Throwable $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        //commit
        DB::commit();
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user with data
        $this->city = City::where('id', $id)
            ->first()
            ->toArray();

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->city = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('cities.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            'city.name_en' => ['required'],
            'city.name_ar' => ['required'],
        ]);
        //set data
        $data = $this->city;
        $data['name_en'] = Filter::RemoveHtml($this->city['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->city['name_ar']);
        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $country = City::findOrFail($id);

            //add log
            AdminLogs::log('edit', 'cities', [
                'old' => $country,
                'new' => $data,
            ], "Edit: customer #$id");

            //update user
            $country->update($data);

            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

        } catch (Exception $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        //commit
        DB::commit();
    }

    /**
     * set modal texts
     */
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

    /**
     * show edit modal
     */
    public function showAddModal()
    {
        //get user with data
        $this->city = [];

        //show the modal
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        //close the modal
        $this->showAddModal = false;

        //empty user data
        $this->city = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('cities.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate([
            'country_id' => ['required', "exists:countries,id"],
            'city.name_en' => ['required'],
            'city.name_ar' => ['required'],
        ]);

        $country = Country::find($this->country_id);

        DB::beginTransaction();
        try {
            City::create([
                'country_code' => $country->code,
                'name_en' => $this->city['name_en'],
                'name_ar' => $this->city['name_ar'],
            ]);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'city',
            ]);

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
