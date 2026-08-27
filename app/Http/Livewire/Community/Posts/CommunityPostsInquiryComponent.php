<?php

namespace App\Http\Livewire\Community\Posts;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Helpers\Notifications;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CommunityPostsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected-soft-delete';
    public $afterTableSlot = 'modals.community.posts.edit';
    public string $afterTableSlot2 = 'modals.community.posts.restore';
    public $model = Post::class;
    public array $post;
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
    private string $country_column = 'name_ar';
    public $governorates = [];
    public $cities = [];

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

        //jump back to the first page: a stale page number left over from the
        //previous tab can land on an out-of-range page and show an empty table
        $this->resetPage();
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
            Column::callback('id', function ($id) {
                $media = Post::withTrashed()->find($id)?->getFirstMedia('posts');

                //local-disk media isn't reachable at getFullUrl() (see MediaResource) —
                //serve it through the request-host media.view route instead; S3 keeps
                //its own working URL. Fall back to the original file when no "thumb"
                //conversion has been generated yet.
                $url = null;
                if ($media) {
                    $isS3 = $media->getDiskDriverName() === 's3';
                    $hasThumb = $media->hasGeneratedConversion('thumb');

                    if ($hasThumb) {
                        $url = $isS3
                            ? $media->getFullUrl('thumb')
                            : route('media.view', ['uuid' => $media->uuid, 'conversion' => 'thumb']);
                    } else {
                        $url = $isS3
                            ? $media->getFullUrl()
                            : route('media.view', ['uuid' => $media->uuid]);
                    }
                }

                return view('admin.pages.community.posts.table-image', ['url' => $url]);
            })
                ->label(__('pages/community/posts/index.datatable.image'))
                ->excludeFromExport()
                ->unsortable(),
            NumberColumn::name('user_id')
                ->label(__('pages/community/posts/index.datatable.user_id'))
                ->searchable()
                ->linkTo('admin/advertisers')
                ->hide(),
            /*Column::callback('user.name')
                ->label(__('pages/community/posts/index.datatable.user_name'))
                ->filterable()
                ->searchable(),*/
            Column::name('advertiser.name')
                ->label(__('pages/community/posts/index.datatable.user_name'))
                ->filterable()
                ->searchable(),
            Column::name("governorate.$this->country_column")
                ->label(__('pages/community/posts/index.datatable.governorate'))
                ->filterable()
                ->searchable(),
            Column::name("city.$this->country_column")
                ->label(__('pages/community/posts/index.datatable.city'))
                ->filterable()
                ->searchable(),
            Column::callback('content', function ($content) {
                return Str::limit($content, 30);
            })
                ->label(__('pages/community/posts/index.datatable.content'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('views_count')
                ->label(__('pages/community/posts/index.datatable.views_count'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('likes_count')
                ->label(__('pages/community/posts/index.datatable.likes_count'))
                ->filterable()
                ->searchable(),
            NumberColumn::name('comments_count')
                ->label(__('pages/community/posts/index.datatable.comments_count'))
                ->filterable()
                ->searchable(),
            /*NumberColumn::name('shares_count')
                ->label(__('pages/community/posts/index.datatable.shares_count'))
                ->filterable()
                ->searchable(),*/
            DateColumn::name('deleted_at')
                ->label(__('datatable.deleted_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->defaultSort('desc'),
            Column::name('status')
                ->label(__('pages/community/posts/index.datatable.status'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'updated_at', 'deleted_at', 'status'], function ($id, $name, $deleted_at, $status) {
                return view('admin.pages.community.posts.table-actions', ['id' => $id, 'name' => $name, 'deleted_at' => $deleted_at, 'status' => $status]);
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
            return Post::onlyTrashed()
                ->whereNull('advertisement_id');
        } else if ($this->page_type === 'active') {
            return Post::withoutTrashed()
                ->whereNull('advertisement_id')
                ->where('posts.status', 'approved');
        }
        else if ($this->page_type === 'unreviewed') {
            return Post::withoutTrashed()
                ->whereNull('advertisement_id')
            ->where('posts.status','pending');
        }
        //"all" — every non-ad post regardless of status, matching its tab count
        return Post::withTrashed()
            ->whereNull('advertisement_id');

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
        if (!Auth::guard('admin')->user()->can('posts.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get advertisers
            $posts = Post::whereIn('id', $this->selected)
                ->get();

            if ($this->delete_type === 'soft') {
                //delete data
                Post::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->each(function ($post) {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->postId', $post->id)
                            ->delete();
                        $post->delete();
                    });
            } else {
                Post::withTrashed()
                    ->whereIn('id', $this->selected)
                    ->get()
                    ->each(function ($post) {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->postId', $post->id)
                            ->delete();
                        $post->forceDelete();
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
            AdminLogs::log('delete', 'posts', [
                'posts' => $posts
            ], "Delete: posts");

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
        $post = Post::withTrashed()
            ->with('advertiser')
            ->where('id', $id)
            ->firstOrFail();

        $this->post = $post->toArray();

        $countryCode = optional($post->advertiser)->country_code;

        $this->governorates = Governorate::select("$this->country_column", 'id')
            ->when($countryCode, fn ($q) => $q->where('country_code', $countryCode))
            ->orderBy($this->country_column)
            ->get()
            ->mapWithKeys(fn ($g) => [$g->id => $g[$this->country_column]]);

        $this->loadCitiesForPost($this->post['governorate_id'] ?? null);

        $this->showEditModal = true;
    }

    /**
     * Reload cities when governorate changes in edit modal.
     */
    public function updatedPostGovernorateId($value)
    {
        $this->post['city_id'] = null;
        $this->loadCitiesForPost($value);
    }

    protected function loadCitiesForPost($governorateId): void
    {
        if (empty($governorateId)) {
            $this->cities = [];
            return;
        }

        $this->cities = City::select("$this->country_column", 'id')
            ->where('governorate_id', $governorateId)
            ->orderBy($this->country_column)
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => $c[$this->country_column]]);
    }

    /**
     * close the modal
     */
    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->post = [];
        $this->governorates = [];
        $this->cities = [];

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
        if (!Auth::guard('admin')->user()->can('posts.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        //validate data
        $this->validate([
            'post.views_count' => ['nullable', 'numeric', 'min:0'],
            'post.likes_count' => ['nullable', 'numeric', 'min:0'],
            'post.comments_count' => ['nullable', 'numeric', 'min:0'],
            'post.status' => ['required', 'in:pending,approved'],
            'post.governorate_id' => ['required', 'exists:governorates,id'],
            'post.city_id' => [
                'required',
                'exists:cities,id',
                Rule::exists('cities', 'id')->where('governorate_id', $this->post['governorate_id'] ?? 0),
            ],
        ]);

        //set data
        $data = [
            'views_count' => Filter::RemoveHtml($this->post['views_count']),
            'likes_count' => Filter::RemoveHtml($this->post['likes_count']),
            'comments_count' => Filter::RemoveHtml($this->post['comments_count']),
            'status' => Filter::RemoveHtml($this->post['status']),
            'governorate_id' => $this->post['governorate_id'],
            'city_id' => $this->post['city_id'],
        ];

        DB::beginTransaction();
        try {
            //get user
            $post = Post::withTrashed()
                ->findOrFail($id);

            //add log
            AdminLogs::log('edit', 'posts', [
                'old' => $post,
                'new' => $data,
            ], "Edit: post #$id");

            //update user
            tap($post)->update($data);


            if ($post->status === 'approved') {
                $this->sendNotificationToIntersetUser($post);
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

    /**
     * One-click approval straight from the actions column, without opening the
     * edit modal. Mirrors the approval side-effects of update().
     *
     * @param $id
     * @return void|null
     */
    public function approve($id)
    {
        if (!Auth::guard('admin')->user()->can('posts.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {
            $post = Post::withTrashed()->findOrFail($id);

            //nothing to do if it is already approved
            if ($post->status === 'approved') {
                DB::rollBack();
                return null;
            }

            AdminLogs::log('edit', 'posts', [
                'old' => $post,
                'new' => ['status' => 'approved'],
            ], "Approve: post #$id");

            tap($post)->update(['status' => 'approved']);

            //notify interested users, exactly as the edit-modal approval does
            $this->sendNotificationToIntersetUser($post);

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //refresh the tab counters so "unreviewed" drops by one
            $this->emitUp('recountCounters');
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

    private function sendNotificationToIntersetUser($post)
    {
        $advertiserCategories = optional($post->advertiser)->categories()->pluck('category_id')->toArray();

        $users_ids = CustomerCategories::whereIn('category_id',$advertiserCategories)->pluck('customer_id')->toArray();
        $advertiser_ids = AdvertiserCategories::whereIn('category_id',$advertiserCategories)->pluck('advertiser_id')->toArray();

        $advertisers = AdvertiserUser::whereIn('id',$advertiser_ids)->where('country_code',$post->user->country_code)->get();
        $users = CustomerUser::whereIn('id',$users_ids)->where('country_code',$post->user->country_code)->get();
        $name = optional($post->advertiser)->name;

        $customProperties = [
            'title'         => " منشور جديد - $name",
            'title_en'         => " منشور جديد - $name",
            'body_en'         => $post->content,
            'notify_link'   => null,
            'postId' => $post->id,
            'userId' => optional($post->advertiser)->id,
            'type'  =>  'posts',
            'userType' => 'advertiser',
            'customProperties' => [
                'postId' => $post->id,
                'type'  =>  'posts',
            ],
        ];

        Notifications::sendFromAdmin($users, 'posts', $post->content, 'add', $customProperties);
        Notifications::sendFromAdmin($advertisers, 'posts', $post->content, 'add', $customProperties);
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/community/posts/index.modal.delete.title'),
            'select-option' => __('pages/community/posts/index.modal.delete.select-option'),
            'soft-delete' => __('pages/community/posts/index.modal.delete.soft-delete'),
            'permanent-delete' => __('pages/community/posts/index.modal.delete.permanent-delete'),
            'content' => __('pages/community/posts/index.modal.delete.content'),
            'cancel' => __('pages/community/posts/index.modal.delete.cancel'),
            'submit' => __('pages/community/posts/index.modal.delete.submit'),
        ];
        $this->editModalTexts = [
            'title' => __('pages/community/posts/index.modal.edit.title'),
            'cancel' => __('pages/community/posts/index.modal.edit.cancel'),
            'submit' => __('pages/community/posts/index.modal.edit.submit'),
        ];
        $this->restoreModalTexts = [
            'title' => __('pages/community/posts/index.modal.restore.title'),
            'content' => __('pages/community/posts/index.modal.restore.content'),
            'cancel' => __('pages/community/posts/index.modal.restore.cancel'),
            'submit' => __('pages/community/posts/index.modal.restore.submit'),
        ];
    }

    /**
     * @param $id
     * @return void|null
     */
    public function restore($id)
    {
        if (!Auth::guard('admin')->user()->can('posts.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        DB::beginTransaction();
        try {

            //restore post
            $post = Post::withTrashed()->find($id)->restore();

            //send toastr alert with success
            $this->alert('success', __('toastr.restored'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('edit', 'posts', [
                'old' => $post,
            ], "Restore: post #$id");

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
