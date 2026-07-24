<?php

namespace App\Http\Livewire\Community\Offers\Comments;

use App\Helpers\Admins\AdminLogs;
use App\Models\Offers\Comments\OffersComments;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CommunityOffersCommentsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected-soft-delete';
    public $afterTableSlot = '';
    public string $afterTableSlot2 = 'modals.community.offers.comments.restore';
    public $model = OffersComments::class;
    public array $comment;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showRestoreModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public array $restoreModalTexts;
    public string $page_type = 'all';
    public string $delete_type = 'soft';
    public bool $has_delete = true;
    public bool $has_restore = true;
    public ?int $restore = null;

    /**
     * @var array
     */
    public $listeners = ['rerenderDataTable' => 'changeType'];

    /**
     * @param $params
     */
    public function changeType($params)
    {
        $this->page_type = $params['page_type'];
    }


    /**
     * AdvertisersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //get admin language
        $this->getAdminLanguage();

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
            Column::name('offer_id')
                ->label(__('pages/community/offers/comments/inquiry.datatable.offer_id'))
                ->filterable()
                ->searchable()
                ->linkTo('admin/community/offers'),
            Column::callback(['id', 'created_at'], function ($id) {
                $user_type = optional(OffersComments::withTrashed()
                    ->findOrFail($id)
                    ->user)->user_type;
                return ucwords($user_type);
            })
                ->label(__('pages/community/offers/comments/inquiry.datatable.user_type'))
                ->searchable()
                ->unsortable(),

            Column::name('user_id')
                ->label(__('pages/community/offers/comments/inquiry.datatable.user_id'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                if ($this->page_type === 'deleted') {
                    $username = OffersComments::onlyTrashed()
                        ->findOrFail($id)
                        ->user->name;
                } else if ($this->page_type === 'active') {
                    $username = OffersComments::where('comments_count', '>', 0)
                        ->findOrFail($id)
                        ->user->name;
                } else {
                    $username = optional(OffersComments::withTrashed()
                        ->findOrFail($id)
                        ->user)->name;
                }
                return ucwords($username);
            })
                ->label(__('pages/community/offers/comments/inquiry.datatable.user_name'))
                ->searchable(),
            Column::callback(['comment'], function ($comment) {
                return Str::limit($comment, 30);
            })
                ->label(__('pages/community/offers/comments/inquiry.datatable.comment'))
                ->filterable()
                ->searchable(),
            DateColumn::name('deleted_at')
                ->label(__('datatable.deleted_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'updated_at', 'deleted_at'], function ($id, $name, $deleted_at) {
                return $deleted_at ? view('admin.pages.community.offers.comments.table-actions', ['id' => $id, 'name' => $name, 'deleted_at' => $deleted_at]) : '-';
            })
                ->label(__('datatable.actions'))
                ->alignCenter()
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        if ($this->page_type === 'deleted') {
            return OffersComments::onlyTrashed();
        }/* else if ($this->page_type === 'active') {
            return OffersComments::where('comments_count', '>', 0);
        }*/
        return OffersComments::withTrashed();
    }

    /**
     * get admin language
     */
    public function getAdminLanguage()
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $this->country_column = 'name_ar';
        } else {
            $this->country_column = 'name_en';
        }
    }

    /**
     * show delete modal
     */
    public function showDeleteModal()
    {
        $this->showDeleteModal = true;
    }

    /**
     * show delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset('delete_type');
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('comments.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get advertisers
            $comments = OffersComments::whereIn('id', $this->selected)
                ->get();

            if ($this->delete_type === 'soft') {
                //delete data
                OffersComments::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->each(function ($comment) {
                        $offer = $comment->offer;
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->offerId', $offer->id)
                            ->whereJsonContains('data->customProperties->commentId', $comment->id)
                            ->delete();

                        $comment->delete();
                    });
            } else {
                OffersComments::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->get()
                    ->each(function ($comment) {
                        $offer = $comment->offer;
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->offerId', $offer->id)
                            ->whereJsonContains('data->customProperties->commentId', $comment->id)
                            ->delete();
                        $comment->forceDelete();
                    });
            }
            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //close modal
            $this->showDeleteModal = false;

            $this->reset('delete_type');
            //add log
            AdminLogs::log('delete', 'comments', [
                'comments' => $comments
            ], "Delete: comments");

            $this->emitUp('recountCounters');
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
    public function showRestoreModal($id)
    {
        //set restore id
        $this->restore = $id;
        //show the modal
        $this->showRestoreModal = true;
    }


    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user with data
        $this->comment = OffersComments::withTrashed()
            ->where('id', $id)
            ->first()
            ->toArray();

        //show the modal
        $this->showEditModal = true;
    }

    /**
     * close the modal
     */
    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->comment = [];

        //reset validation messages
        $this->resetValidation();
    }

    /**
     * update user data
     * @param $id
     * @return void|null
     */
    public function update($id)
    {
        if (!Auth::guard('admin')->user()->can('comments.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'user.name' => ['required', "unique:advertisers_users,name,$id"],
        ]);

        //set data
        $data = $this->comment;

        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $comment = OffersComments::findOrFail($id);

            //add log
            AdminLogs::log('edit', 'comments', [
                'old' => $comment,
                'new' => $data,
            ], "Edit: comment #$id");

            //update user
            $comment->update($data);

            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();
            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->emitUp('recountCounters');
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
            'title' => __('pages/community/offers/comments/inquiry.modal.delete.title'),
            'select-option' => __('pages/community/offers/comments/inquiry.modal.delete.select-option'),
            'soft-delete' => __('pages/community/offers/comments/inquiry.modal.delete.soft-delete'),
            'permanent-delete' => __('pages/community/offers/comments/inquiry.modal.delete.permanent-delete'),
            'content' => __('pages/community/offers/comments/inquiry.modal.delete.content'),
            'cancel' => __('pages/community/offers/comments/inquiry.modal.delete.cancel'),
            'submit' => __('pages/community/offers/comments/inquiry.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/community/offers/comments/inquiry.modal.edit.title'),
            'cancel' => __('pages/community/offers/comments/inquiry.modal.edit.cancel'),
            'submit' => __('pages/community/offers/comments/inquiry.modal.edit.submit'),
        ];
        $this->restoreModalTexts = [
            'title' => __('pages/community/offers/comments/inquiry.modal.restore.title'),
            'content' => __('pages/community/offers/comments/inquiry.modal.restore.content'),
            'cancel' => __('pages/community/offers/comments/inquiry.modal.restore.cancel'),
            'submit' => __('pages/community/offers/comments/inquiry.modal.restore.submit'),
        ];
    }


    /**
     * @param $id
     * @return void|null
     */
    public function restore($id)
    {
        if (!Auth::guard('admin')->user()->can('comments.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {

            //restore comment
            $comment = OffersComments::withTrashed()->find($id)->restore();

            //send toastr alert with success
            $this->alert('success', __('toastr.restored'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('edit', 'comments', [
                'old' => $comment,
            ], "Restore: comment #$id");

            $this->reset('restore');
            $this->showRestoreModal = false;

            $this->emitUp('recountCounters');
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
}
