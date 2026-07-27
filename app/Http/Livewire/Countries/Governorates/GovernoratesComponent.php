<?php

namespace App\Http\Livewire\Countries\Governorates;

use App\Models\Countries\Governorates\Governorate;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class GovernoratesComponent extends Component
{
    use LivewireAlert;

    private ?string $governorate_id = null;
    private ?Governorate $governorate = null;
    private bool $order = false;

    protected $listeners = [
        'setGovernorateId' => 'setGovernorate',
    ];

    public function render()
    {
        if ($this->governorate_id) {
            $this->governorate = Governorate::where('id', $this->governorate_id)->first();
        }

        return view('admin.pages.countries.governorates.inquiry', [
            'governorate' => $this->governorate ?? null,
            'order' => $this->order ?? false,
        ]);
    }

    public function setGovernorate($governorate_id = null, bool $order = false)
    {
        $this->governorate_id = $governorate_id ?: null;
        $this->order = $order;
    }
}
