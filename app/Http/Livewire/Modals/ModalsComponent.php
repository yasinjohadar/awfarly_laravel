<?php

namespace App\Http\Livewire\Modals;

use App\Models\Modals\Modal;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ModalsComponent extends Component
{
    use LivewireAlert;

    private ?string $modal_id = null;
    private ?Modal $modal = null;
    private bool $order = false;

    protected $listeners = [
        'setModalId' => 'setModal',
    ];

    public function render()
    {
        if ($this->modal_id) {
            $this->modal = Modal::where('id', $this->modal_id)
                ->first();
        }
        return view('admin.pages.modals.inquiry', ['modal' => $this->modal ?? null, 'order' => $this->order ?? false]);
    }

    /**
     * @param null $modal_id
     * @param bool $order
     */
    public function setModal($modal_id = null, bool $order = false)
    {
        if (!$modal_id) {
            $this->modal_id = null;
        } else {
            $this->modal_id = $modal_id;
        }
        $this->order = $order;
    }
}
