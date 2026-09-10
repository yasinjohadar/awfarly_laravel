<?php

namespace App\Http\Livewire;

use App\Models\Offers\Offer;
use App\Models\Posts\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NavbarNotifications extends Component
{
    public int $pending_posts_count = 0;
    public int $pending_offers_count = 0;

    protected $listeners = ['recountCounters' => 'recount'];

    public function mount()
    {
        $this->recount();
    }

    public function recount()
    {
        $admin = Auth::guard('admin')->user();

        $this->pending_posts_count = $admin->can('posts.inquiry')
            ? Post::where('status', 'pending')->whereNull('advertisement_id')->count()
            : 0;

        $this->pending_offers_count = $admin->can('offers.inquiry')
            ? Offer::where('status', 'pending')->count()
            : 0;
    }

    public function render()
    {
        return view('livewire.navbar-notifications');
    }
}
