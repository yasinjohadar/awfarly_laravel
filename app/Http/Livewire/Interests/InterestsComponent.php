<?php

namespace App\Http\Livewire\Interests;

use App\Models\Interests\Interest;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class InterestsComponent extends Component
{
    use LivewireAlert;

    private ?string $interest_id = null;
    private ?Interest $interest = null;
    private bool $order = false;

    protected $listeners = [
        'setInterestId' => 'setInterest',
    ];

    public function render()
    {
        if ($this->interest_id) {
            $this->interest = Interest::where('id', $this->interest_id)
                ->first();
        }
        return view('admin.pages.interests.inquiry', ['interest' => $this->interest ?? null, 'order' => $this->order ?? false]);
    }

    /**
     * @param null $interest_id
     * @param bool $order
     */
    public function setInterest($interest_id = null, bool $order = false)
    {
        if (!$interest_id) {
            $this->interest_id = null;
        } else {
            $this->interest_id = $interest_id;
        }
        $this->order = $order;
    }
}
