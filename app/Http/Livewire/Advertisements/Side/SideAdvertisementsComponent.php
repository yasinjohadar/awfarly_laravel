<?php

namespace App\Http\Livewire\Advertisements\Side;

use App\Models\Advertisements\Side\SideAdvertisement;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class SideAdvertisementsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $advertisement_id = null;
    protected $listeners = ['setAdvertisementId', 'recountCounters'];
    public ?int $all_advertisements_count = null;
    public ?int $active_advertisements_count = null;
    public ?int $expired_advertisements_count = null;


    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        $this->all_advertisements_count = SideAdvertisement::count();

        $this->active_advertisements_count = SideAdvertisement::where('ends_at', '>', now())
            ->orWhereNull('ends_at')
            ->count();

        $this->expired_advertisements_count = SideAdvertisement::where('ends_at', '<', now())
            ->count();

        return view('livewire.pages.advertisements.side.index', [
            'advertisement_id' => $this->advertisement_id,
        ]);
    }

    /**
     * @param $active
     */
    public function changeActiveTab($active)
    {
        $this->page_type = $active;

        $this->emit('rerenderDataTable', ['page_type' => $active]);
    }

    /**
     * @param $id
     */
    public function setAdvertisementId($id = null)
    {
        $this->advertisement_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_advertisements_count = SideAdvertisement::count();

        $this->active_advertisements_count = SideAdvertisement::where('ends_at', '>', now())
            ->orWhereNull('ends_at')
            ->count();

        $this->expired_advertisements_count = SideAdvertisement::where('ends_at', '<', now())
            ->count();
    }
}
