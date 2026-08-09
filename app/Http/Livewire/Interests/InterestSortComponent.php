<?php

namespace App\Http\Livewire\Interests;

use App\Models\Interests\Interest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class InterestSortComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    public ?int $interest_id = null;
    public array $order = [];
    public string $language_column = 'name_ar';

    protected $listeners = [
        'showAddModal'
    ];

    /**
     * dispatch event to load scripts in the view
     */
    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    public function render()
    {
        $this->language_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';
        if ($this->interest_id) {
            $interests = Interest::where('parent_interest_id', $this->interest_id)
                ->orderBy('order')
                ->get()
                ->map(function ($interest) {
                    return [
                        'name' => $interest->{$this->language_column},
                        'id' => $interest->id,
                    ];
                });
        } else {
            $interests = Interest::whereNull('parent_interest_id')
                ->orderBy('order')
                ->get()
                ->map(function ($interest) {
                    return [
                        'name' => $interest->{$this->language_column},
                        'id' => $interest->id,
                    ];
                });
        }
        return view('admin.pages.interests.sort', [
            'interests' => $interests,
            'order' => $interests->pluck('id'),
        ]);
    }

    /**
     * set new order for interests
     */
    public function setOrder($orders)
    {
        DB::beginTransaction();
        try {
            foreach ($orders as $index => $order) {
                Interest::where('id', $order)
                    ->first()
                    ->update([
                        'order' => $index + 1
                    ]);
            }
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
        //send toastr alert with success
        $this->alert('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ]);
    }
}
