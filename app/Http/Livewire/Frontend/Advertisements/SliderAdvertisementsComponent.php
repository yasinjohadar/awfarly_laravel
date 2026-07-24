<?php

namespace App\Http\Livewire\Frontend\Advertisements;

use App\Models\Advertisements\Slider\SliderAdvertisement;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class SliderAdvertisementsComponent extends Component
{
    public Collection $slide_advertisements;

    public function mount()
    {
        $this->slide_advertisements = SliderAdvertisement::inRandomOrder()
            ->whereHas('media')
            ->where(function ($q) {
                return $q->where('ends_at', '>', now())
                    ->OrWhereNull('ends_at');
            })
            ->get();
    }

    public function render()
    {
        return view('livewire.frontend.advertisements.slider');
    }
}
