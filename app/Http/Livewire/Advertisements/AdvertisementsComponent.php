<?php

namespace App\Http\Livewire\Advertisements;

use App\Models\Advertisements\Advertisement;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AdvertisementsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $advertisement_id = null;
    protected $listeners = ['setAdvertisementId', 'recountCounters'];
    public int $all_advertisements_count = 0;
    public int $active_advertisements_count = 0;
    public int $expired_advertisements_count = 0;


    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        $this->all_advertisements_count = Advertisement::count();

        $this->active_advertisements_count = Advertisement::where(function ($q) {
            return $q->where('ends_at', '>', now())
                ->orWhereNull('ends_at');
        })
            ->where('is_active', true)
            ->count();

        $this->expired_advertisements_count = Advertisement::where('ends_at', '<', now())
            ->orWhere('is_active', false)
            ->count();

        return view('livewire.pages.advertisements.index', [
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
        $this->all_advertisements_count = Advertisement::count();

        $this->active_advertisements_count = Advertisement::where(function ($q) {
            return $q->where('ends_at', '>', now())
                ->orWhereNull('ends_at');
        })
            ->where('is_active', true)
            ->count();

        $this->expired_advertisements_count = Advertisement::where('ends_at', '<', now())
            ->orWhere('is_active', false)
            ->count();
    }
}
