<?php

namespace App\Http\Livewire\Advertisements;

use App\Helpers\Files;
use App\Models\Advertisements\Advertisement;
use App\Models\Categories\Category;
use App\Models\Countries\Cities\City;
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

class AdvertisementsShowComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public int $advertisement_id;
    public bool $showEditModal = false;
    public array $order = [];
    public $media;
    public bool $showDeleteModal = false;
    public ?int $image_id = null;
    public string $content;
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
        //get advertisement
        $advertisement = Advertisement::where('id', $this->advertisement_id)
            ->first();

        $advertisement['starts_at'] = $advertisement->starts_at ? Carbon::parse($advertisement->starts_at)->format('Y-m-d h:i A') : '-';
        $advertisement['ends_at'] = $advertisement->ends_at ? Carbon::parse($advertisement->ends_at)->format('Y-m-d h:i A') : '-';

        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $name_column = 'name_ar';
        } else {
            $name_column = 'name_en';
        }
        //get categories
        $categories = Category::whereIn('id', $advertisement->categories ?? [])
            ->get()
            ->map(function ($category) use ($name_column) {
                return $category->{$name_column};
            })
            ->toArray();

        //implode categories to one string
        $categories = $categories ? implode(", ", $categories) : '-';

        //get cities
        $cities = City::whereIn('id', $advertisement->cities ?? [])
            ->get()
            ->map(function ($city) use ($name_column) {
                return $city->{$name_column};
            })
            ->toArray();

        //implode cities to one string
        $cities = $cities ? implode(", ", $cities) : '-';

        return view('admin.pages.advertisements.inquiry', [
            'advertisement' => $advertisement,
            'showEditModal' => $this->showEditModal,
            'categories' => $categories,
            'cities' => $cities,
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
        ]);

        $advertisement = Advertisement::where('id', $id)
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
                        $file_width = Image::load($media)->getWidth();
                        $file_height = Image::load($media)->getHeight();
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $file = $media->store('uploads/temp', 'local');
                    $advertisement->addMediaFromDisk($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('advertisements');
                    Files::deleteS3File($file);
                }
            }

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset(['media']);

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
     * @return null
     */
    public function deleteImage($id)
    {
        //get advertisement
        $advertisement = Advertisement::where('id', $this->advertisement_id)
            ->first();

        //get media
        $media = $advertisement->getMedia('advertisements')->where('id', $id)
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

            $advertisement = Advertisement::where('id', $this->advertisement_id)
                ->first();

            $media_data = $advertisement->getMedia('advertisements')
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
        //get advertisement
        $advertisement = Advertisement::where('id', $this->advertisement_id)
            ->first();

        $this->order = $advertisement->getMedia('advertisements')
            ->pluck('id')
            ->toArray() ?? [];
    }
}
