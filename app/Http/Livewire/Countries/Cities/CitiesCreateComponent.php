<?php

namespace App\Http\Livewire\Countries\Cities;

use App\Helpers\Filter;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class CitiesCreateComponent extends Component
{
    use LivewireAlert;

    public string $country_code = '';
    public string $name_en = '';
    public string $name_ar = '';
    private string $country_column = '';

    protected array $rules = [
        'country_code' => ['required', 'exists:countries,code'],
        'name_en' => ['required'],
        'name_ar' => ['required'],
    ];

    public function __construct($id = null)
    {
        //get admin language
        $this->getAdminLanguage();

        parent::__construct($id);
    }

    /**
     * get admin language
     */
    public function getAdminLanguage()
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $this->country_column = 'name_ar';
        } else {
            $this->country_column = 'name_en';
        }
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //select all countries
        $countries = Country::select(
            "$this->country_column",
            'code'
        )
            ->get()
            ->map(function ($country) {
                return [
                    'country_code' => $country->code,
                    'name' => $country[$this->country_column],
                ];
            });

        return view('livewire.pages.countries.cities.create', ['countries' => $countries]);
    }

    /**
     * @return null
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('cities.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate();
        DB::beginTransaction();
        try {
            $data = [
                'country_code' => $this->country_code,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
            ];
            City::create($data);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'country_code',
                'name_en',
                'name_ar',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
    }
}
