<?php

namespace App\Http\Livewire\Advertisers\Ratings;

use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AdvertisersRatingsComponent extends Component
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
        $this->all_count = AdvertiserRatings::count();

        $this->approved_count = AdvertiserRatings::where('status', 'approved')
            ->count();

        $this->pending_count = AdvertiserRatings::where('status', 'pending')
            ->count();

        $this->unapproved_count = AdvertiserRatings::where('status', 'unapproved')
            ->count();

        return view('livewire.pages.advertisers.ratings.index', [
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
        $this->all_count = AdvertiserRatings::count();

        $this->approved_count = AdvertiserRatings::where('status', 'approved')
            ->count();

        $this->pending_count = AdvertiserRatings::where('status', 'pending')
            ->count();

        $this->unapproved_count = AdvertiserRatings::where('status', 'unapproved')
            ->count();
    }
}
