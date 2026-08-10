<?php

namespace App\Http\Livewire\Countries;

use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CountriesComponent extends Component
{
    use LivewireAlert;

    public ?string $country_id = null;
    private ?Country $country = null;
    public ?string $governorate_id = null;
    private ?Governorate $governorate = null;
    public bool $order = false;

    protected $listeners = [
        'setCountryId' => 'setCountry',
        'setGovernorateId' => 'setGovernorate',
    ];

    public function render()
    {
        if ($this->country_id) {
            $this->country = Country::where('id', $this->country_id)
                ->first();
        }

        if ($this->governorate_id) {
            $this->governorate = Governorate::where('id', $this->governorate_id)
                ->first();
        }

        return view('admin.pages.countries.inquiry', [
            'country' => $this->country ?? null,
            'governorate' => $this->governorate ?? null,
            'order' => $this->order ?? false,
        ]);
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
        $this->governorate_id = null;
        $this->order = $order;
    }

    /**
     * @param null $governorate_id
     * @param bool $order
     */
    public function setGovernorate($governorate_id = null, bool $order = false)
    {
        $this->governorate_id = $governorate_id ?: null;
        $this->order = $order;
    }
}
