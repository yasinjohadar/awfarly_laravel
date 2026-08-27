<?php

namespace App\Http\Livewire\Community\Posts;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Helpers\Notifications;
use App\Models\Categories\Category;
use App\Models\Posts\Post;
use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Categories\AdvertiserCategories;
use App\Models\Users\Customers\Categories\CustomerCategories;
use App\Models\Users\Customers\CustomerUser;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class CommunityPostsShowComponent extends Component
{
    use LivewireAlert;
    use WithFileUploads;

    public int $post_id;
    public bool $showEditModal = false;
    public array $order = [];
    public $media;
    public bool $showDeleteModal = false;
    public ?int $image_id = null;
    public string $content = '';
    public ?int $category_id = null;

    public function mount()
    {
        $this->setOrderData();

        $this->setFormData();
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get post
        $post = Post::withTrashed()
            ->with('user')
            ->where('id', $this->post_id)
            ->first();

        $post['created_at'] = isset($post['created_at']) ? Carbon::make($post['created_at'])->format('Y-m-d h:i A') : null;

        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $name_column = 'name_ar';
        } else {
            $name_column = 'name_en';
        }

        //current post category
        $post['category_name'] = $post->category ? $post->category->{$name_column} : '-';

        $categories = Category::where('parent_category_id', null)
            ->get()
            ->map(function ($category) use ($name_column) {
                if ($category->has('childCategories') && count($category->childCategories) > 0) {
                    return [
                        'name' => $category->{$name_column},
                        'children' => $category->childCategories()
                            ->get()
                            ->map(function ($child) use ($name_column) {
                                return [
                                    'id' => $child->id,
                                    'name' => $child->{$name_column},
                                ];
                            }),
                    ];
                }
                return [
                    'id' => $category->id,
                    'name' => $category->{$name_column},
                ];
            });
        return view('admin.pages.community.posts.inquiry', [
            'post' => $post,
            'showEditModal' => $this->showEditModal,
            'categories' => $categories,
        ]);
    }

    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    /**
     * approve a pending post — mirrors CommunityPostsInquiryComponent::approve()
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

    private function sendNotificationToIntersetUser($post)
    {
        $advertiserCategories = optional($post->advertiser)->categories()->pluck('category_id')->toArray();

        $users_ids = CustomerCategories::whereIn('category_id', $advertiserCategories)->pluck('customer_id')->toArray();
        $advertiser_ids = AdvertiserCategories::whereIn('category_id', $advertiserCategories)->pluck('advertiser_id')->toArray();

        $advertisers = AdvertiserUser::whereIn('id', $advertiser_ids)->where('country_code', $post->user->country_code)->get();
        $users = CustomerUser::whereIn('id', $users_ids)->where('country_code', $post->user->country_code)->get();
        $name = optional($post->advertiser)->name;

        $customProperties = [
            'title' => " منشور جديد - $name",
            'title_en' => " منشور جديد - $name",
            'body_en' => $post->content,
            'notify_link' => null,
            'postId' => $post->id,
            'userId' => optional($post->advertiser)->id,
            'type' => 'posts',
            'userType' => 'advertiser',
            'customProperties' => [
                'postId' => $post->id,
                'type' => 'posts',
            ],
        ];

        Notifications::sendFromAdmin($users, 'posts', $post->content, 'add', $customProperties);
        Notifications::sendFromAdmin($advertisers, 'posts', $post->content, 'add', $customProperties);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //load the current post data into the form
        $this->setFormData();

        //reset validation messages
        $this->resetValidation();

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;


        //reset validation messages
        $this->resetValidation();
    }

    public function update($id)
    {
        $this->validate([
            'media' => ['nullable', 'max:10000',],
            'content' => ['nullable', 'string',],
            'category_id' => ['nullable', 'exists:categories,id',],
        ]);

        $post = Post::withTrashed()
            ->where('id', $id)
            ->first();

        DB::beginTransaction();
        try {

            //close modal
            $this->closeEditModal();

            if ($this->media) {
                foreach ($this->media as $media) {
                    $mime_type = $media->getMimeType();
                    if (strstr($mime_type, "video/")) {
                        $file_width = null;
                        $file_height = null;
                    } else if (strstr($mime_type, 'image/')) {
                        $file_width = Image::load($media->getRealPath())->getWidth();
                        $file_height = Image::load($media->getRealPath())->getHeight();
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $file = $media->store('uploads/temp', 'local');
                    $post->addMediaFromDisk($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('posts');
                    Files::deleteS3File($file);
                }
            }

            $post->update([
                'content' => $this->content,
                'category_id' => $this->category_id,
            ]);
            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset(['media']);

            $post = Post::withTrashed()
                ->with('user')
                ->where('id', $this->post_id)
                ->first();

            /*$media_data = $post->getMedia('posts')
                ->map(function ($item) {
                    return [
                        'alt' => $item->name,
                        'src' => Files::mediaUrl($item),
                        'subHtml' => '',
                        'thumb' => Files::mediaUrl($item, 'thumb'),
                        'width' => 140,
                    ];
                });*/
            $this->setOrderData();

            $this->dispatchBrowserEvent('resetLightGallery'/*, json_encode($media_data)*/);

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput', $this->order);
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
        DB::commit();
    }

    /**
     * set new order for files
     */
    public function setOrder($data)
    {
        DB::beginTransaction();
        try {

            Media::setNewOrder($data);

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->order = $data;

            $this->dispatchBrowserEvent('getData');
        } catch (Throwable $e) {
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
    }

    /**
     * @param $id
     */
    public function deleteImage($id)
    {
        //get post
        $post = Post::withTrashed()
            ->with('user')
            ->where('id', $this->post_id)
            ->first();

        //get media
        $media = $post->getMedia('posts')->where('id', $id)
            ->first();
        DB::beginTransaction();
        try {

            //delete media
            $media->delete();

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset([
                'image_id'
            ]);

            $this->showDeleteModal = false;

            $post = Post::withTrashed()
                ->with('user')
                ->where('id', $this->post_id)
                ->first();

            $media_data = $post->getMedia('posts')
                ->map(function ($item) {
                    return [
                        'alt' => $item->name,
                        'src' => Files::mediaUrl($item),
                        'subHtml' => '',
                        'thumb' => Files::mediaUrl($item, 'thumb'),
                        'width' => 140,
                    ];
                });

            $this->dispatchBrowserEvent('resetLightGallery', json_encode($media_data));
        } catch (Throwable $e) {
            DB::rollBack();
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();


    }

    /**
     * show delete modal
     */
    public function showDeleteModal($id)
    {
        $this->image_id = $id;
        $this->showDeleteModal = true;
    }

    /**
     * show delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    /**
     * load the editable post data into the form properties
     */
    public function setFormData()
    {
        //get post
        $post = Post::withTrashed()
            ->where('id', $this->post_id)
            ->first();

        $this->content = $post->content ?? '';
        $this->category_id = $post->category_id;
    }

    public function setOrderData()
    {
        $this->order = [];
        //get post
        $post = Post::withTrashed()
            ->with('user')
            ->where('id', $this->post_id)
            ->first();

        $this->order = $post->getMedia('posts')
            ->pluck('id')
            ->toArray();
    }
}
