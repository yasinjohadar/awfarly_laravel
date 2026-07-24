<?php

namespace App\Http\Livewire\Frontend\Advertisements;

use App\Helpers\Settings;
use App\Models\Advertisements\Side\SideAdvertisement;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class LeftSideAdvertisementsComponent extends Component
{
    public Collection $left_side;

    public function mount()
    {
        $limit = Settings::Get('posts.pagination.limit', 10);

        $this->left_side = SideAdvertisement::whereHas('media')
            ->where(function ($q) {
                return $q->where('ends_at', '>', now())
                    ->OrWhereNull('ends_at');
            })
            ->where('side', 'left')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.frontend.advertisements.left-side');
    }
}
