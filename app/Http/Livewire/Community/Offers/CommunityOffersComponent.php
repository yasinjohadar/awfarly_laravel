<?php

namespace App\Http\Livewire\Community\Offers;

use App\Models\Offers\Offer;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityOffersComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $offer_id = null;
    protected $listeners = ['setOfferId', 'recountCounters'];
    public ?int $all_offers_count = null;
    public ?int $active_offers_count = null;
    public ?int $unreviewed_offers_count = null;
    public ?int $expired_offers_count = null;
    public ?int $deleted_offers_count = null;
    public ?int $filter_id = null;

    public function render()
    {
        $this->all_offers_count = Offer::withTrashed()
            ->count();

        $this->active_offers_count = Offer::where('expires_at', '>', Carbon::now())
            ->where('status', 'approved')
            ->count();

        $this->unreviewed_offers_count = Offer::where('status', 'pending')
            ->count();

        $this->expired_offers_count = Offer::where('expires_at', '<', Carbon::now())
            ->count();

        $this->deleted_offers_count = Offer::onlyTrashed()
            ->count();

        if ($this->filter_id) {
            $activeNumberFilters = [
                '1' => [
                    'start' => $this->filter_id,
                    'end' => $this->filter_id,
                ]
            ];
        }

        return view('livewire.pages.community.offers.index', [
            'offer_id' => $this->offer_id,
            'activeNumberFilters' => $activeNumberFilters ?? []
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
    public function setOfferId($id = null)
    {
        $this->offer_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_offers_count = Offer::withTrashed()
            ->count();

        $this->active_offers_count = Offer::where('expires_at', '>', Carbon::now())
            ->where('status', 'approved')
            ->count();

        $this->unreviewed_offers_count = Offer::where('status', 'pending')
            ->count();

        $this->expired_offers_count = Offer::where('expires_at', '<', Carbon::now())
            ->count();

        $this->deleted_offers_count = Offer::onlyTrashed()
            ->count();
    }
}
