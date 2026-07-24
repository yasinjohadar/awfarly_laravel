<?php

namespace App\Http\Livewire\Countries;

use App\Helpers\Filter;
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

class CountriesCreateComponent extends Component
{
    use LivewireAlert;

    public string $code = '';
    public string $name_en = '';
    public string $name_ar = '';
    public string $mobile_code = '';

    protected array $rules = [
        'code' => ['required', 'unique:countries,code'],
        'name_en' => ['required'],
        'name_ar' => ['required'],
        /*'mobile_code' => ['required', 'unique:countries,mobile_code'],*/
    ];

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        return view('livewire.pages.countries.create');
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('countries.add')) {
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
                'code' => strtoupper(Filter::RemoveHtml($this->code)),
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
            ];
            Country::create($data);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'code',
                'name_en',
                'name_ar',
                /*'mobile_code',*/
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
