<?php

namespace App\Http\Livewire\Community\Offers;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\FCM\FcmHelper;
use App\Helpers\Filter;
use App\Helpers\Notifications;
use App\Models\Offers\Offer;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
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

class CommunityOffersInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected-soft-delete';
    public $afterTableSlot = 'modals.community.offers.edit';
    public string $afterTableSlot2 = 'modals.community.offers.restore';
    public $model = Offer::class;
    public array $offer;
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
                ->searchable()
                ->width('50px'),
            NumberColumn::name('advertiser.id')
                ->label(__('pages/community/offers/inquiry.datatable.user_id'))
                ->filterable()
                ->searchable()
                ->width('50px')
                ->linkTo('admin/advertisers'),
            Column::name('advertiser.name')
                ->label(__('pages/community/offers/inquiry.datatable.user_name'))
                ->filterable()
                ->searchable(),
            Column::callback('content', function ($content) {
                return Str::limit($content, 30);
            })
                ->label(__('pages/community/offers/inquiry.datatable.content'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('sale_percentage')
                ->label(__('pages/community/offers/inquiry.datatable.sale_percentage'))
                ->filterable()
                ->searchable(),
            Column::name('advertisement_url')
                ->label(__('pages/community/offers/inquiry.datatable.advertisement_url'))
                ->filterable()
                ->searchable(),
            DateColumn::name('expires_at')
                ->label(__('pages/community/offers/inquiry.datatable.expires_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            NumberColumn::name('expires_in')
                ->label(__('pages/community/offers/inquiry.datatable.expires_in'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback('rate', function ($rate) {
                return $rate ?: '-';
            })
                ->label(__('pages/community/offers/inquiry.datatable.rate'))
                ->filterable()
                ->searchable()
                ->hide(),
            NumberColumn::name('likes_count')
                ->label(__('pages/community/offers/inquiry.datatable.likes_count'))
                ->filterable()
                ->searchable()
                ->hide(),
            NumberColumn::name('views_count')
                ->label(__('pages/community/offers/inquiry.datatable.views_count'))
                ->filterable()
                ->searchable()
                ->hide(),
            NumberColumn::name('comments_count')
                ->label(__('pages/community/offers/inquiry.datatable.comments_count'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::name('deleted_at')
                ->label(__('pages/community/offers/inquiry.datatable.deleted_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'updated_at', 'deleted_at'], function ($id, $name, $deleted_at) {
                return view('admin.pages.community.offers.table-actions', ['id' => $id, 'name' => $name, 'deleted_at' => $deleted_at]);
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
        if ($this->page_type === 'deleted') {
            return Offer::onlyTrashed();
        } else if ($this->page_type === 'active') {
            return Offer::where('expires_at', '>', Carbon::now())
                ->where('offers.status', 'approved');
        } else if ($this->page_type === 'unreviewed') {
            return Offer::where('offers.status', 'pending');
        } else if ($this->page_type === 'expired') {
            return Offer::where('expires_at', '<', Carbon::now());
        }
        return Offer::withTrashed();
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
        if (!Auth::guard('admin')->user()->can('offers.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get advertisers
            $offers = Offer::whereIn('id', $this->selected)
                ->get();

            if ($this->delete_type === 'soft') {
                //delete data
                Offer::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->get()
                    ->each(function ($offer) {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->offerId', $offer->id)
                            ->delete();
                        $offer->delete();
                    });
            } else {
                Offer::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->get()
                    ->each(function ($offer) {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->offerId', $offer->id)
                            ->delete();
                        $offer->forceDelete();
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
            AdminLogs::log('delete', 'offers', [
                'offers' => $offers
            ], "Delete: offers");

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
        $this->offer = Offer::withTrashed()
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
        $this->offer = [];

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
        if (!Auth::guard('admin')->user()->can('offers.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'offer.content' => ['nullable'],
            'offer.sale_percentage' => ['nullable', 'numeric', 'min:0'],
            'offer.advertisement_url' => ['nullable', 'url'],
            'offer.expires_in' => ['nullable', 'numeric', 'min:0'],
            'offer.status' => ['required', 'in:pending,approved'],
            'offer.views_count' => ['nullable', 'numeric', 'min:0'],
            'offer.likes_count' => ['nullable', 'numeric', 'min:0'],
            'offer.comments_count' => ['nullable', 'numeric', 'min:0'],
        ]);

        //set data
        $data = [
            'content' => Filter::RemoveHtml($this->offer['content']),
            'sale_percentage' => Filter::RemoveHtml($this->offer['sale_percentage']),
            'advertisement_url' => Filter::RemoveHtml($this->offer['advertisement_url']),
            'expires_in' => Filter::RemoveHtml($this->offer['expires_in']),
            'status' => Filter::RemoveHtml($this->offer['status']),
            'views_count' => Filter::RemoveHtml($this->offer['views_count']),
            'likes_count' => Filter::RemoveHtml($this->offer['likes_count']),
            'comments_count' => Filter::RemoveHtml($this->offer['comments_count']),
            'rate' => !empty($this->offer['rate']) ? $this->offer['rate'] : null,
        ];


        DB::beginTransaction();
        try {
            //get user
            $offer = Offer::withTrashed()
                ->findOrFail($id);

            if ($this->offer['status'] === 'approved') {
                if ($offer->status === $this->offer['status'] && $data['expires_in'] !== $offer->expires_in && $data['expires_in'] > 0) {
                    $expires_at = $offer->expires_at ? Carbon::make($offer->expires_at)->subDays($offer->expires_in) : Carbon::now();
                    $data['expires_at'] = $expires_at->addDays($data['expires_in']);
                } else if ($offer->status !== $this->offer['status'] && $data['expires_in'] > 0) {
                    $data['expires_at'] = Carbon::now()->addDays($data['expires_in']);
                } else {
                    $data['expires_at'] = null;
                }
            } else {
                $data['expires_at'] = null;
            }
                //add log
            AdminLogs::log('edit', 'offers', [
                'old' => $offer,
                'new' => $data,
            ], "Edit: offer #$id");

            //update user
            tap($offer)->update($data);

            if ($offer->status === 'approved') {
                $this->sendNotificationToIntersetUser($offer);
            }

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

    private function sendNotificationToIntersetUser($offer)
    {
        $advertiserCategories = optional($offer->advertiser)->categories()->pluck('category_id')->toArray();

        $users_ids = CustomerCategories::whereIn('category_id',$advertiserCategories)->pluck('customer_id')->toArray();
        $advertiser_ids = AdvertiserCategories::whereIn('category_id',$advertiserCategories)->pluck('advertiser_id')->toArray();

        $advertisers = AdvertiserUser::whereIn('id',$advertiser_ids)->where('country_code',$offer->advertiser->country_code)->get();
        $users = CustomerUser::whereIn('id',$users_ids)->where('country_code',$offer->advertiser->country_code)->get();
        $name = optional($offer->advertiser)->name;


        $customProperties = [
            'title'         => " اعلان جديد - $name",
            'title_en'         => " اعلان جديد - $name",
            'body_en'         => $offer->content,
            'notify_link'   => null,
            'offerId' => $offer->id,
            'type'  =>  'offers',
            'message' => "offers.add",
            'userId' => optional($offer->advertiser)->id,
            'userType' => 'advertiser',
            'customProperties' => [
                'offerId' => $offer->id,
                'type'  =>  'offers',

            ],
        ];

        Notifications::sendFromAdmin($users, 'offers', $offer->content, 'add', $customProperties);
        Notifications::sendFromAdmin($advertisers, 'offers', $offer->content, 'add', $customProperties);
        foreach($users->pluck('fcm_token')->toArray() as $token){

            FcmHelper::sendFcmNotification([
                'title'         => " اعلان جديد - $name",
                'title_en'         => " اعلان جديد - $name",
                'body'              => $offer->content,
                'body_en'         => $offer->content,
                'type'  =>  'offers',
                'offerId' => $offer->id,
                'message' => "offers.add",
                'action' => "like",
                'customProperties' => $customProperties,
            ], [$token]);
        }

        foreach($advertisers->pluck('fcm_token')->toArray() as $token) {

            FcmHelper::sendFcmNotification([
                'title' => " اعلان جديد - $name",
                'title_en' => " اعلان جديد - $name",
                'body' => $offer->content,
                'body_en' => $offer->content,
                'type' => 'offers',
                'offerId' => $offer->id,
                'message' => "offers.add",
                'action' => "like",
                'customProperties' => $customProperties,
            ], [$token]);
        }
    }
    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/community/offers/inquiry.modal.delete.title'),
            'select-option' => __('pages/community/offers/inquiry.modal.delete.select-option'),
            'soft-delete' => __('pages/community/offers/inquiry.modal.delete.soft-delete'),
            'permanent-delete' => __('pages/community/offers/inquiry.modal.delete.permanent-delete'),
            'content' => __('pages/community/offers/inquiry.modal.delete.content'),
            'cancel' => __('pages/community/offers/inquiry.modal.delete.cancel'),
            'submit' => __('pages/community/offers/inquiry.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/community/offers/inquiry.modal.edit.title'),
            'cancel' => __('pages/community/offers/inquiry.modal.edit.cancel'),
            'submit' => __('pages/community/offers/inquiry.modal.edit.submit'),
        ];
        $this->restoreModalTexts = [
            'title' => __('pages/community/offers/inquiry.modal.restore.title'),
            'content' => __('pages/community/offers/inquiry.modal.restore.content'),
            'cancel' => __('pages/community/offers/inquiry.modal.restore.cancel'),
            'submit' => __('pages/community/offers/inquiry.modal.restore.submit'),
        ];
    }

    /**
     * @param $id
     * @return void|null
     */
    public function restore($id)
    {
        if (!Auth::guard('admin')->user()->can('offers.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {

            //restore offer
            $offer = Offer::withTrashed()->find($id)->restore();

            //send toastr alert with success
            $this->alert('success', __('toastr.restored'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('edit', 'offers', [
                'old' => $offer,
            ], "Restore: offer #$id");

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
