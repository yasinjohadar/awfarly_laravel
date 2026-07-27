<?php

namespace App\Http\Livewire\Countries\Governorates;

use App\Helpers\Filter;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class GovernoratesCreateComponent extends Component
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
        $this->getAdminLanguage();
        parent::__construct($id);
    }

    public function getAdminLanguage()
    {
        $countryColumn = Auth::guard('admin')->user()->language_code;
        $this->country_column = $countryColumn === 'ar' ? 'name_ar' : 'name_en';
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        $countries = Country::select("$this->country_column", 'code')
            ->get()
            ->map(function ($country) {
                return [
                    'country_code' => $country->code,
                    'name' => $country[$this->country_column],
                ];
            });

        return view('livewire.pages.countries.governorates.create', ['countries' => $countries]);
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('governorates.add')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        $this->validate();
        DB::beginTransaction();
        try {
            Governorate::create([
                'country_code' => $this->country_code,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
            ]);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset(['country_code', 'name_en', 'name_ar']);
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
