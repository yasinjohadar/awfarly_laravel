<?php

namespace App\Http\Livewire\Countries\Cities;

use App\Helpers\Filter;
use App\Models\Countries\Cities\City;
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

class CitiesCreateComponent extends Component
{
    use LivewireAlert;

    public string $governorate_id = '';
    public string $name_en = '';
    public string $name_ar = '';
    private string $name_column = '';

    protected array $rules = [
        'governorate_id' => ['required', 'exists:governorates,id'],
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
        $this->name_column = $countryColumn === 'ar' ? 'name_ar' : 'name_en';
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        $governorates = Governorate::select("$this->name_column", 'id')
            ->orderBy('country_code')
            ->orderBy('order')
            ->get()
            ->map(function ($governorate) {
                return [
                    'governorate_id' => $governorate->id,
                    'name' => $governorate[$this->name_column],
                ];
            });

        return view('livewire.pages.countries.cities.create', ['governorates' => $governorates]);
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('cities.add')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            return null;
        }

        $this->validate();
        DB::beginTransaction();
        try {
            City::create([
                'governorate_id' => $this->governorate_id,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
            ]);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset(['governorate_id', 'name_en', 'name_ar']);
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
