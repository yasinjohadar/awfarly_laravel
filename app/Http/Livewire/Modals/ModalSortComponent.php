<?php

namespace App\Http\Livewire\Modals;

use App\Models\Modals\Modal;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ModalSortComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public ?int $modal_id = null;
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
        if ($this->modal_id) {
            $modals = Modal::where('parent_modal_id', $this->modal_id)
                ->orderBy('order')
                ->get()
                ->map(function ($modal) {
                    return [
                        'name' => $modal->{$this->language_column},
                        'id' => $modal->id,
                    ];
                });
        } else {
            $modals = Modal::whereNull('parent_modal_id')
                ->orderBy('order')
                ->get()
                ->map(function ($modal) {
                    return [
                        'name' => $modal->{$this->language_column},
                        'id' => $modal->id,
                    ];
                });
        }
        return view('admin.pages.modals.sort', ['modals' => $modals]);
    }

    /**
     * set new order for files
     */
    public function setOrder($orders)
    {
        DB::beginTransaction();
        try {
            foreach ($orders as $index => $order) {
                Modal::where('id', $order)
                    ->first()
                    ->update([
                        'order' => $index + 1
                    ]);
            }
            /*$this->dispatchBrowserEvent('getData');*/
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
