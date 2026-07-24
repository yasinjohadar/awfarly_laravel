<?php

namespace App\Http\Livewire\Advertisements\Side;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Advertisements\Side\SideAdvertisement;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class SideAdvertisementsShowComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public int $advertisement_id;
    public bool $showEditModal = false;
    public array $order = [];
    public bool $showDeleteModal = false;
    public string $content;
    public ?int $image_id = null;
    public ?string $advertisement_url = null;
    public $image;
    public ?string $side = 'right';
    public ?string $starts_at = null;
    public ?string $ends_at = null;


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
        $advertisement = SideAdvertisement::where('id', $this->advertisement_id)
            ->first();
        $this->side = $advertisement->side;
        $this->advertisement_url = $advertisement->advertisement_url;
        $this->starts_at = $advertisement->starts_at ? Carbon::parse($advertisement->starts_at)->format('Y-m-d\TH:m') : null;
        $this->ends_at = $advertisement->ends_at ? Carbon::parse($advertisement->ends_at)->format('Y-m-d\TH:m') : null;

        $advertisement['starts_at'] = $advertisement->starts_at ? Carbon::parse($advertisement->starts_at)->format('Y-m-d h:i A') : '-';
        $advertisement['ends_at'] = $advertisement->ends_at ? Carbon::parse($advertisement->ends_at)->format('Y-m-d h:i A') : '-';

        return view('admin.pages.advertisements.side.inquiry', [
            'advertisement' => $advertisement,
            'showEditModal' => $this->showEditModal
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
            'advertisement_url' => ['required', 'url'],
            'side' => ['required', 'in:right,left'],
            'image' => ['nullable', 'image'],
            'starts_at' => ['required'],
            'ends_at' => ['nullable', 'after:starts_at'],
        ]);

        $advertisement = SideAdvertisement::where('id', $id)
            ->first();

        DB::beginTransaction();
        try {

            //close modal
            $this->closeEditModal();

            if ($this->image) {
                $advertisement->clearMediaCollection('advertisements');
                $image = Image::make($this->image);
                $mime_type = $this->image->getMimeType();
                if (strstr($mime_type, "video/")) {
                    $file_width = null;
                    $file_height = null;
                } else if (strstr($mime_type, 'image/')) {
                    $file_width = $image->getWidth();
                    $file_height = $image->getHeight();
                } else {
                    $file_width = null;
                    $file_height = null;
                }
                /*$file = $this->image->store('uploads/temp', 'local');
                $url = Storage::get($file);*/
                $advertisement->addMedia($image->basePath())
                    ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                    ->toMediaCollection('advertisements');
                /*Files::deleteS3File($file);*/
            }

            $advertisement->update([
                'advertisement_url' => Filter::RemoveHtml($this->advertisement_url),
                'side' => $this->side,
                'starts_at' => Carbon::parse($this->starts_at),
                'ends_at' => $this->ends_at ? Carbon::parse($this->ends_at) : null,
            ]);
            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset(['image']);

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
     * @param $id
     * @return null
     */
    public function deleteImage($id)
    {
        //get advertisement
        $advertisement = SideAdvertisement::where('id', $this->advertisement_id)
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

            $advertisement = SideAdvertisement::where('id', $this->advertisement_id)
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
        $advertisement = SideAdvertisement::where('id', $this->advertisement_id)
            ->first();

        $this->order = $advertisement->getMedia('advertisements')
            ->pluck('id')
            ->toArray();
    }
}
