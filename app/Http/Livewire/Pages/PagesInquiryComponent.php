<?php

namespace App\Http\Livewire\Pages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Pages\Page;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class PagesInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = '';
    public $afterTableSlot = '';/*modals.pages.edit*/
    public $model = Page::class;
    public array $page_data;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public ?string $contents_en = null, $contents_ar = null;

    public bool $has_delete = true;

    /**
     * CustomersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {

        //set modal texts
        $this->setModalTexts();
        $this->dispatchBrowserEvent('reloadSummernote');

        parent::__construct($id);
    }

    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            /*Column::checkbox(),*/
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            Column::name('slug')
                ->label(__('pages/pages/index.content.datatable.slug'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('title_en')
                ->label(__('pages/pages/index.content.datatable.title_en'))
                ->filterable()
                ->searchable(),
            Column::name('title_ar')
                ->label(__('pages/pages/index.content.datatable.title_ar'))
                ->filterable()
                ->searchable(),
            Column::callback('contents_en', function ($contents_en) {
                return Str::limit(Filter::RemoveHtml($contents_en), 30);
            })
                ->label(__('pages/pages/index.content.datatable.contents_en'))
                ->filterable()
                ->searchable(),
            Column::callback('contents_ar', function ($contents_ar) {
                return Str::limit(Filter::RemoveHtml($contents_ar), 30);
            })
                ->label(__('pages/pages/index.content.datatable.contents_ar'))
                ->filterable()
                ->searchable(),
            BooleanColumn::name('is_protected')
                ->label(__('pages/pages/index.content.datatable.is_protected'))
                ->filterable()
                ->searchable()
                ->hide(),
            BooleanColumn::name('is_active')
                ->label(__('pages/pages/index.content.datatable.is_active'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id'], function ($id) {
                return view('admin.pages.pages.table-actions', ['id' => $id]);
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
        return Page::selectRaw('*');
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
        if (!Auth::guard('admin')->user()->can('customers.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {
            //get pages
            $pages = Page::whereIn('id', $this->selected)
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
            AdminLogs::log('delete', 'pages', [
                'pages' => $pages
            ], "Delete: pages");

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
        //get page_data
        $this->page_data = Page::where('id', $id)
            ->select('id', 'contents_en', 'contents_ar')
            ->first()
            ->toArray();

        $this->contents_en = $this->page_data['contents_en'];
        $this->contents_ar = $this->page_data['contents_ar'];
        /*$this->page_data['is_protected'] = (int)$this->page_data['is_protected'];
        $this->page_data['is_active'] = (int)$this->page_data['is_active'];*/
        $this->dispatchBrowserEvent('reload-summernote');
        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty page_data
        $this->page_data = [];
        $this->reset([
            'contents_en',
            'contents_ar',
        ]);
        //reset validation messages
        $this->resetValidation();
    }

    /**
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('pages.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //validate data
        $this->validate([
            /*'page_data.slug' => ['required', "unique:pages,slug,$id"],
            'page_data.title_en' => ['required', "unique:pages,title_en,$id"],
            'page_data.title_ar' => ['required', "unique:pages,title_ar,$id"],*/
            'contents_en' => ['required'],
            'contents_ar' => ['required'],
            /*'page_data.is_protected' => ['required', 'boolean'],
            'page_data.is_active' => ['required', 'boolean'],*/
        ]);
        //set data
        $data = $this->page_data;
        $data['contents_en'] = $this->contents_en;
        $data['contents_ar'] = $this->contents_ar;

        //unset the page_data id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $page_data = Page::findOrFail($id);

            //add log
            AdminLogs::log('edit', 'customers', [
                'old' => $page_data,
                'new' => $data,
            ], "Edit: customer #$id");

            //update page_data
            $page_data->update($data);

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
            'title' => __('pages/pages/index.modal.delete.title'),
            'content' => __('pages/pages/index.modal.delete.content'),
            'cancel' => __('pages/pages/index.modal.delete.cancel'),
            'submit' => __('pages/pages/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/pages/index.modal.edit.title'),
            'cancel' => __('pages/pages/index.modal.edit.cancel'),
            'submit' => __('pages/pages/index.modal.edit.submit'),
        ];
    }
}
