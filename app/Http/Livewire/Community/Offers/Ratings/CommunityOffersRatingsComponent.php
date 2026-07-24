<?php

namespace App\Http\Livewire\Community\Offers\Ratings;

use App\Models\Offers\Ratings\OfferRatings;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityOffersRatingsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $rating_id = null;
    protected $listeners = ['setRatingId', 'recountCounters'];
    public ?int $all_count = null;
    public ?int $approved_count = null;
    public ?int $pending_count = null;
    public ?int $unapproved_count = null;


    public function render()
    {
        $this->all_count = OfferRatings::count();

        $this->approved_count = OfferRatings::where('status', 'approved')
            ->count();

        $this->pending_count = OfferRatings::where('status', 'pending')
            ->count();

        $this->unapproved_count = OfferRatings::where('status', 'unapproved')
            ->count();

        return view('livewire.pages.community.offers.ratings.index', [
            'rating_id' => $this->rating_id,
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
    public function setRatingId($id = null)
    {
        $this->rating_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_count = OfferRatings::count();

        $this->approved_count = OfferRatings::where('status', 'approved')
            ->count();

        $this->pending_count = OfferRatings::where('status', 'pending')
            ->count();

        $this->unapproved_count = OfferRatings::where('status', 'unapproved')
            ->count();
    }
}
