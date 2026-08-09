<?php

namespace App\Http\Livewire\Countries;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Countries\Country;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CountriesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.countries.edit';
    public $model = Country::class;
    public array $country;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public bool $has_delete = true;

    /**
     * countriesInquiryComponent constructor.
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
            Column::name('code')
                ->label(__('pages/countries/index.content.datatable.code'))
                ->filterable()
                ->searchable(),
            Column::name("name_en")
                ->label(__('pages/countries/index.content.datatable.name_en'))
                ->filterable()
                ->searchable(),
            Column::name("name_ar")
                ->label(__('pages/countries/index.content.datatable.name_ar'))
                ->filterable()
                ->searchable(),
            /*Column::name('mobile_code')
                ->label(__('pages/countries/index.content.datatable.mobile_code'))
                ->filterable()
                ->searchable(),*/
            NumberColumn::callback(['id', 'created_at'], function ($id) {
                return Country::where('id', $id)
                    ->first()
                    ->governorates()
                    ->count();
            })
                ->label(__('pages/countries/index.content.datatable.governorates_count'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_active')
                ->label(__('pages/countries/index.content.datatable.active'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.countries.table-actions', ['id' => $id]);
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
        return Country::with('governorates');
    }

    /**
     * show delete modal for selected rows, or a single country by id
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
     * Set delete modal title/content based on selection count and country name
     */
    protected function setDeleteModalTextsForSelection(): void
    {
        $base = 'pages/countries/index.modal.delete';

        if (count($this->selected) === 1) {
            $country = Country::find($this->selected[0]);
            $name = '';
            if ($country) {
                $name = App::currentLocale() === 'ar'
                    ? ($country->name_ar ?: $country->name_en)
                    : ($country->name_en ?: $country->name_ar);
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

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('countries.delete')) {
            //send toastr alert with error
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
            //get countries
            $countries = Country::whereIn('id', $this->selected)
                ->withCount('governorates')
                ->get();

            //block delete if a country still has governorates or referenced users
            $inUse = $countries->first(function ($country) {
                return $country->governorates_count > 0;
            });

            if (!$inUse) {
                $codes = $countries->pluck('code');
                $hasUsers = AdvertiserUser::whereIn('country_code', $codes)->exists()
                    || CustomerUser::whereIn('country_code', $codes)->exists();

                if ($hasUsers) {
                    $inUse = true;
                }
            }

            if ($inUse) {
                DB::rollBack();
                $this->alert('error', __('pages/countries/index.modal.delete.in_use'), [
                    'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                ]);
                $this->showDeleteModal = false;
                return null;
            }

            //delete data
            parent::delete($this->selected);

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'countries', [
                'countries' => $countries
            ], "Delete: countries");

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
        $this->country = Country::where('id', $id)
            ->first()
            ->toArray();

        $this->country['is_active'] = $this->country['is_active'] ? 1 : 0;

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->country = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('countries.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            'country.code' => ['required', "unique:countries,code,$id"],
            'country.name_en' => ['required'],
            'country.name_ar' => ['required'],
            'country.is_active' => ['boolean'],
            /*'country.mobile_code' => ['required', "unique:countries,mobile_code,$id"],*/
        ]);
        //set data
        $data = $this->country;
        $data['code'] = Filter::RemoveHtml($this->country['code']);
        $data['name_en'] = Filter::RemoveHtml($this->country['name_en']);
        $data['name_ar'] = Filter::RemoveHtml($this->country['name_ar']);
        $data['is_active'] = $this->country['is_active'];
        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $country = Country::findOrFail($id);

            //add log
            AdminLogs::log('edit', 'countries', [
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
            'title' => __('pages/countries/index.modal.delete.title'),
            'content' => __('pages/countries/index.modal.delete.content'),
            'cancel' => __('pages/countries/index.modal.delete.cancel'),
            'submit' => __('pages/countries/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/countries/index.modal.edit.title'),
            'cancel' => __('pages/countries/index.modal.edit.cancel'),
            'submit' => __('pages/countries/index.modal.edit.submit'),
        ];
    }
}
