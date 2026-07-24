<?php

namespace App\Http\Livewire\Community\Offers;

use App\Helpers\Files;
use App\Models\Categories\Category;
use App\Models\Offers\Offer;
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

class CommunityOffersShowComponent extends Component
{
    use WithFileUploads;    use LivewireAlert;


    public int $offer_id;
    public bool $showEditModal = false;
    public array $order = [];
    public $media;
    public bool $showDeleteModal = false;
    public ?int $image_id = null;
    public array $offerData = [];
    public ?int $category_id = null;

    public function mount()
    {
        $this->setOrderData();
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get offer
        $offer = Offer::withTrashed()
            ->where('id', $this->offer_id)
            ->first();

        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $name_column = 'name_ar';
        } else {
            $name_column = 'name_en';
        }

        $this->offerData = $offer
            ->toArray();

        $this->offerData['expires_at'] = $offer->expires_at ? Carbon::make($offer['expires_at'])->format("Y-m-d\TH:i") : null;

        $this->category_id = $offer['category_id'];

        $offer['category_name'] = $offer->category->{$name_column} ?? null;
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
        return view('admin.pages.community.offers.inquiry', [
            'offer' => $offer,
            'showEditModal' => $this->showEditModal,
            'categories' => $categories,
        ]);
    }

    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
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
            'offerData.content' => ['nullable', 'string',],
            'offerData.category_id' => ['nullable', 'exists:categories,id',],
            'offerData.sale_percentage' => ['nullable', 'numeric',],
            'offerData.advertisement_url' => ['nullable', 'url',],
            'offerData.expires_at' => ['nullable', 'date_format:Y-m-d\TH:i',],
            'offerData.expires_in' => ['nullable', 'numeric',],
            'offerData.status' => ['nullable', 'in:approved,pending',],
            'offerData.rate' => ['nullable', 'numeric', 'min:0', 'max:5',],
            'offerData.views_count' => ['nullable', 'numeric',],
            'offerData.likes_count' => ['nullable', 'numeric',],
            'offerData.comments_count' => ['nullable', 'numeric',],
        ]);

        $offer = Offer::withTrashed()
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
                    $offer->addMediaFromDisk($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('offers');
                    Files::deleteS3File($file);
                }
            }

            if ($this->offerData['status'] === 'approved') {
                if ($offer->status !== $this->offerData['status'] && $this->offerData['expires_in'] > 0) {
                    $this->offerData['expires_at'] = Carbon::now()->addDays($this->offerData['expires_in']);
                } else if ($offer->status === $this->offerData['status'] && $this->offerData['expires_in'] !== $offer->expires_in && $this->offerData['expires_in'] > 0) {
                    $expires_at = $offer->expires_at ? Carbon::make($offer->expires_at)->subDays($offer->expires_in) : Carbon::now();
                    $this->offerData['expires_at'] = $expires_at->addDays($this->offerData['expires_in']);
                } else {
                    $this->offerData['expires_at'] = $offer->expires_at;
                }
            } else {
                $this->offerData['expires_at'] = null;
            }

            $offer->update([
                'category_id' => $this->category_id,
                'content' => $this->offerData['content'],
                'sale_percentage' => $this->offerData['sale_percentage'],
                'advertisement_url' => $this->offerData['advertisement_url'],
                'expires_at' => $this->offerData['expires_at'] ? Carbon::make($this->offerData['expires_at']) : null,
                'expires_in' => $this->offerData['expires_in'],
                'status' => $this->offerData['status'],
                'rate' => $this->offerData['rate'],
                'views_count' => $this->offerData['views_count'],
                'likes_count' => $this->offerData['likes_count'],
                'comments_count' => $this->offerData['comments_count'],
            ]);
            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset(['media']);

            $offer = Offer::withTrashed()
                ->where('id', $this->offer_id)
                ->first();

            /*$media_data = $offer->getMedia('offers')
                ->map(function ($item) {
                    return [
                        'alt' => $item->name,
                        'src' => $item->getUrl(),
                        'subHtml' => '',
                        'thumb' => $item->getUrl('thumb'),
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
     * @return void|null
     */
    public function deleteImage($id)
    {
        //get offer
        $offer = Offer::withTrashed()
            ->where('id', $this->offer_id)
            ->first();

        //get media
        $media = $offer->getMedia('offers')->where('id', $id)
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

            $offer = Offer::withTrashed()
                ->where('id', $this->offer_id)
                ->first();

            $media_data = $offer->getMedia('offers')
                ->map(function ($item) {
                    return [
                        'alt' => $item->name,
                        'src' => $item->getUrl(),
                        'subHtml' => '',
                        'thumb' => $item->getUrl('thumb'),
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

    public function setOrderData()
    {
        $this->order = [];
        //get offer
        $offer = Offer::withTrashed()
            ->where('id', $this->offer_id)
            ->first();

        $this->order = $offer->getMedia('offers')
            ->pluck('id')
            ->toArray();
    }
}
