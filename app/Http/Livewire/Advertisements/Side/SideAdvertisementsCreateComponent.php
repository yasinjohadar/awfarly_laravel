<?php

namespace App\Http\Livewire\Advertisements\Side;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Advertisements\Side\SideAdvertisement;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Intervention\Image\Facades\Image;
use Throwable;

class SideAdvertisementsCreateComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    public ?string $advertisement_url = null;
    public $image;
    public ?string $side = 'right';
    public ?string $starts_at = null;
    public ?string $ends_at = null;

    protected array $rules = [
        'advertisement_url' => ['required', 'url'],
        'side' => ['required', 'in:right,left'],
        'image' => ['required', 'image'],
        'starts_at' => ['required'],
        'ends_at' => ['nullable', 'after:starts_at'],
    ];

    public function render()
    {
        $this->starts_at = Carbon::now()->format('Y-m-d\TH:m');

        return view('livewire.pages.advertisements.side.create');
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate();
        DB::beginTransaction();
        try {
            $data = [
                'advertisement_url' => Filter::RemoveHtml($this->advertisement_url),
                'side' => $this->side,
                'starts_at' => Carbon::parse($this->starts_at),
                'ends_at' => $this->ends_at ? Carbon::parse($this->ends_at) : null,
            ];

            $advertisement = SideAdvertisement::create($data);

            if ($this->image) {
                $mime_type = $this->image->getMimeType();
                $image = Image::make($this->image);

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

            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'advertisement_url',
                'side',
                'starts_at',
                'ends_at',
                'image',
            ]);
            $this->dispatchBrowserEvent('clearFileInput');
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
}
