<?php

namespace App\Http\Livewire\Community\Proposals;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Proposals\Proposal;
use App\Models\Users\Advertisers\AdvertiserUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CommunityProposalsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected-soft-delete';
    public $afterTableSlot = 'modals.community.proposals.edit';
    public string $afterTableSlot2 = 'modals.community.proposals.restore';
    public $model = Proposal::class;
    public array $proposal;
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

        //reset to the first page so a stale page number from the previous
        //tab cannot land on an out-of-range page and show an empty table
        $this->resetPage();
    }


    /**
     * AdvertisersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //set modal texts
        $this->setModalTexts();

        parent::__construct($id);
    }

    public function initialiseSort()
    {
        $this->sort = 1;
        $this->direction = false;
    }

    public function mount($model = null, $include = [], $exclude = [], $hide = [], $dates = [], $times = [], $searchable = [], $sort = null, $hideHeader = null, $hidePagination = null, $perPage = null, $exportable = false, $hideable = false, $beforeTableSlot = false, $afterTableSlot = false, $params = [])
    {
        parent::mount($model, $include, $exclude, $hide, $dates, $times, $searchable, $sort, $hideHeader, $hidePagination, $perPage, $exportable, $hideable, $beforeTableSlot, $afterTableSlot, $params);
        $this->initialiseSort();
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
                ->defaultSort(false)
                ->label('#')
                ->filterable()
                ->searchable(),
            NumberColumn::name('advertiser_id')
                ->label(__('pages/community/proposals/index.datatable.advertiser_id'))
                ->filterable()
                ->searchable()
                ->linkTo('admin/advertisers'),
            Column::name('advertiser.name')
                ->label(__('pages/community/proposals/index.datatable.advertiser_name'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('user_id')
                ->label(__('pages/community/proposals/index.datatable.user_id'))
                ->filterable()
                ->searchable(),
            Column::callback('user_type', function ($user_type) {
                return $user_type === AdvertiserUser::class ?
                    __('pages/community/proposals/index.datatable.users_types.advertiser') :
                    __('pages/community/proposals/index.datatable.users_types.customer');
            })
                ->label(__('pages/community/proposals/index.datatable.user_type')),
            Column::callback(['id'], function ($id) {
                $proposal = Proposal::withTrashed()
                    ->findOrFail($id);
                return ucwords($proposal->user->name);
            })
                ->label(__('pages/community/proposals/index.datatable.user_name')),
            Column::callback('content', function ($content) {
                return Str::limit($content, 30);
            })
                ->label(__('pages/community/proposals/index.datatable.content'))
                ->filterable()
                ->searchable(),
            DateColumn::callback(['expires_in', 'answered_at'], function ($expires_in, $answered_at) {
                return $expires_in ? Carbon::make($answered_at)->addDays($expires_in)->diffForHumans() : '-';
            })
                ->label(__('pages/community/proposals/index.datatable.expires_at'))
                /*->filterable()
                ->searchable()*/
                ->hide(),
            NumberColumn::callback('expires_in', function ($expires_in) {
                return $expires_in ?: '-';
            })
                ->label(__('pages/community/proposals/index.datatable.expires_in'))
                ->filterable()
                ->searchable()
                ->sortBy(DB::raw('DATE_FORMAT(DATE_ADD(answered_at, INTERVAL expires_in DAY), "%m%d%Y")'))
                ->hide(),
            DateColumn::callback('answered_at', function ($answered_at) {
                return $answered_at ? Carbon::make($answered_at)->diffForHumans() : '-';
            })
                ->label(__('pages/community/proposals/index.datatable.answered_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
//            Column::callback(['id', 'updated_at', 'deleted_at'], function ($id, $name, $deleted_at) {
//                $proposal = Proposal::withTrashed()
//                    ->where('id', $id)
//                    ->first();
//                $has_answer = (bool)$proposal->answer;
//
//                return view('admin.pages.community.proposals.table-actions', ['id' => $id, 'name' => $name, 'deleted_at' => $deleted_at, 'has_answer' => $has_answer]);
//            })
//                ->label(__('datatable.actions'))
//                ->excludeFromExport()
//                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        if ($this->page_type === 'answered') {
            return Proposal::withTrashed()
                ->whereNotNull('answer');
        } else if ($this->page_type === 'unanswered') {
            return Proposal::withTrashed()
                ->whereNull('answer');
        }
        return Proposal::withTrashed();
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
        if (!Auth::guard('admin')->user()->can('proposals.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get advertisers
            $proposals = Proposal::whereIn('id', $this->selected)
                ->get();

            if ($this->delete_type === 'soft') {
                //delete data
                Proposal::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->each(function ($proposal) {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->proposalId', $proposal->id)
                            ->delete();
                        $proposal->delete();
                    });
            } else {
                Proposal::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->get()
                    ->each(function ($proposal) {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->proposalId', $proposal->id)
                            ->delete();
                        $proposal->forceDelete();
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
            AdminLogs::log('delete', 'proposals', [
                'proposals' => $proposals
            ], "Delete: proposals");

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
        $this->proposal = Proposal::withTrashed()
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
        $this->proposal = [];

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
        if (!Auth::guard('admin')->user()->can('proposals.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'proposal.content' => ['required'],
            'proposal.answer' => ['nullable'],
        ]);

        //set data
        $data = $this->proposal;
        $data['content'] = Filter::RemoveHtml($this->proposal['content']);
        $data['answer'] = Filter::RemoveHtml($this->proposal['answer']);
        //unset the user id
        unset($data['id']);

        DB::beginTransaction();
        try {
            //get user
            $proposal = Proposal::withTrashed()
                ->findOrFail($id);

            //add log
            AdminLogs::log('edit', 'proposals', [
                'old' => $proposal,
                'new' => $data,
            ], "Edit: proposal #$id");

            //update user
            $proposal->update($data);

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
            'title' => __('pages/community/proposals/index.modal.delete.title'),
            'select-option' => __('pages/community/proposals/index.modal.delete.select-option'),
            'soft-delete' => __('pages/community/proposals/index.modal.delete.soft-delete'),
            'permanent-delete' => __('pages/community/proposals/index.modal.delete.permanent-delete'),
            'content' => __('pages/community/proposals/index.modal.delete.content'),
            'cancel' => __('pages/community/proposals/index.modal.delete.cancel'),
            'submit' => __('pages/community/proposals/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/community/proposals/index.modal.edit.title'),
            'cancel' => __('pages/community/proposals/index.modal.edit.cancel'),
            'submit' => __('pages/community/proposals/index.modal.edit.submit'),
        ];
        $this->restoreModalTexts = [
            'title' => __('pages/community/proposals/index.modal.restore.title'),
            'content' => __('pages/community/proposals/index.modal.restore.content'),
            'cancel' => __('pages/community/proposals/index.modal.restore.cancel'),
            'submit' => __('pages/community/proposals/index.modal.restore.submit'),
        ];
    }

    /**
     * @param $id
     * @return void|null
     */
    public function restore($id)
    {
        if (!Auth::guard('admin')->user()->can('proposals.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {

            //restore Proposal
            $proposal = Proposal::withTrashed()->find($id)->restore();

            //send toastr alert with success
            $this->alert('success', __('toastr.restored'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('edit', 'proposals', [
                'old' => $proposal,
            ], "Restore: proposal #$id");

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
