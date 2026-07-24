<?php

namespace App\Http\Livewire\Countries;

use App\Models\Countries\Country;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CountriesComponent extends Component
{
    use LivewireAlert;

    private ?string $country_id = null;
    private ?Country $country = null;
    private bool $order = false;

    protected $listeners = [
        'setCountryId' => 'setCountry'
    ];

    public function render()
    {
        if ($this->country_id) {
            $this->country = Country::where('id', $this->country_id)
                ->first();
        }
        return view('admin.pages.countries.inquiry', ['country' => $this->country ?? null, 'order' => $this->order ?? false]);
    }

    /**
     * @param null $country_id
     * @param bool $order
     */
    public function setCountry($country_id = null, bool $order = false)
    {
        if (!$country_id) {
            $this->country_id = null;
        } else {
            $this->country_id = $country_id;
        }
        $this->order = $order;
    }
}
