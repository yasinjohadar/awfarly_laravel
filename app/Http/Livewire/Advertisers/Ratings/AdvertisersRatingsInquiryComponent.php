<?php

namespace App\Http\Livewire\Advertisers\Ratings;

use App\Helpers\Admins\AdminLogs;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
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

class AdvertisersRatingsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;

    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $afterTableSlot = 'modals.users.advertisers.rating.edit';
    public $model = AdvertiserRatings::class;
    public array $rating;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showRestoreModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public array $restoreModalTexts;
    public string $page_type = 'all';
    public bool $has_delete = true;
    public ?int $restore = null;
    public ?string $status = null;
    public ?string $comment = null;
    public ?float $rate = null;

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
            NumberColumn::name('advertiser_id')
                ->label(__('pages/advertisers/ratings/index.content.datatable.advertiser_id'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::name('advertiser.name')
                ->label(__('pages/advertisers/ratings/index.content.datatable.advertiser_name'))
                ->filterable()
                ->searchable(),
            Column::callback(['advertisers_ratings.id', 'created_at'], function ($id) {
                $user_type = AdvertiserRatings::findOrFail($id)
                    ->user->user_type;
                return __("pages/advertisers/ratings/index.content.datatable.user_types.{$user_type}");
            })
                ->label(__('pages/advertisers/ratings/index.content.datatable.user_type'))
                ->searchable()
                ->unsortable()
                ->hide(),
            NumberColumn::name('user_id')
                ->label(__('pages/advertisers/ratings/index.content.datatable.user_id'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'advertiser_id'], function ($id) {
                $username = AdvertiserRatings::findOrFail($id)
                    ->user->name;
                return ucwords($username);
            })
                ->label(__('pages/advertisers/ratings/index.content.datatable.user_name'))
                ->searchable(),
            NumberColumn::name('rate')
                ->label(__('pages/advertisers/ratings/index.content.datatable.rate'))
                ->filterable()
                ->searchable(),
            Column::callback('comment', function ($comment) {
                return Str::limit($comment, 30);
            })
                ->label(__('pages/advertisers/ratings/index.content.datatable.comment'))
                ->filterable()
                ->searchable(),
            Column::callback('status', function ($status) {
                return __("pages/advertisers/ratings/index.content.datatable.status_types.{$status}");
            })
                ->label(__('pages/advertisers/ratings/index.content.datatable.status'))
                ->filterable([
                    'approved' => __("pages/advertisers/ratings/index.content.datatable.status_types.approved"),
                    'pending' => __("pages/advertisers/ratings/index.content.datatable.status_types.pending"),
                    'unapproved' => __("pages/advertisers/ratings/index.content.datatable.status_types.unapproved"),
                ])
                ->searchable(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'created_at'], function ($id) {
                return view('admin.pages.advertisers.ratings.table-actions', ['id' => $id]);
            })
                ->label(__('datatable.actions'))
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
        if ($this->page_type === 'approved') {
            return AdvertiserRatings::where('advertisers_ratings.status', 'approved')
                ->join('advertisers_users', 'advertisers_users.id', 'advertisers_ratings.advertiser_id');
        } else if ($this->page_type === 'pending') {
            return AdvertiserRatings::where('advertisers_ratings.status', 'pending')
                ->join('advertisers_users', 'advertisers_users.id', 'advertisers_ratings.advertiser_id');
        } else if ($this->page_type === 'unapproved') {
            return AdvertiserRatings::where('advertisers_ratings.status', 'unapproved')
                ->join('advertisers_users', 'advertisers_users.id', 'advertisers_ratings.advertiser_id');
        }
        return AdvertiserRatings::join('advertisers_users', 'advertisers_users.id', 'advertisers_ratings.advertiser_id');
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
        if (!Auth::guard('admin')->user()->can('ratings.inquiry')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get customers
            $ratings = AdvertiserRatings::whereIn('id', $this->selected)
                ->get();

            AdvertiserRatings::whereIn('id', $this->selected)
                ->get()
                ->each(function ($rate) {
                    $advertiser = $rate->advertiser;
                    $rate->delete();
                    $advertiser->update([
                        'rate' => $advertiser->rating()
                            ->where('status', 'approved')
                            ->avg('rate'),
                    ]);
                });

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'advertisers_ratings', [
                'ratings' => $ratings
            ], "Delete: Advertisers ratings");

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
     * show delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
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
        $this->rating = AdvertiserRatings::where('id', $id)
            ->first()
            ->toArray();

        $this->status = $this->rating['status'];
        $this->rate = $this->rating['rate'];
        $this->comment = $this->rating['comment'];
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
        $this->rating = [];

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
        if (!Auth::guard('admin')->user()->can('ratings.approve')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'status' => ['required', 'in:approved,pending,unapproved'],
            'comment' => ['nullable'],
            'rate' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        //set data
        $data = [
            'status' => $this->status,
            'comment' => $this->comment,
            'rate' => $this->rate,
        ];

        DB::beginTransaction();
        try {
            //get user
            $rating = AdvertiserRatings::findOrFail($id);


            //add log
            AdminLogs::log('edit', 'ratings', [
                'old' => $rating,
                'new' => $data,
            ], "Edit: rating #$id");

            //update user
            $rating->update($data);

            $advertiser_rating = AdvertiserRatings::where('advertiser_id', $rating->advertiser_id)
                ->where('status', 'approved')
                ->avg('rate');

            AdvertiserUser::where('id', $rating->advertiser_id)
                ->update([
                    'rate' => $advertiser_rating
                ]);


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
        $this->editModalTexts = [
            'title' => __('pages/advertisers/ratings/index.modal.edit.title'),
            'cancel' => __('pages/advertisers/ratings/index.modal.edit.cancel'),
            'submit' => __('pages/advertisers/ratings/index.modal.edit.submit'),
        ];
        $this->deleteModalTexts = [
            'title' => __('pages/advertisers/ratings/index.modal.delete.title'),
            'content' => __('pages/advertisers/ratings/index.modal.delete.content'),
            'cancel' => __('pages/advertisers/ratings/index.modal.delete.cancel'),
            'submit' => __('pages/advertisers/ratings/index.modal.delete.submit'),
        ];
    }

}
