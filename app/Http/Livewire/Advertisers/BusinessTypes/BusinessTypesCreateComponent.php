<?php

namespace App\Http\Livewire\Advertisers\BusinessTypes;

use App\Helpers\Filter;
use App\Models\Users\Advertisers\BusinessTypes\AdvertiserBusinessType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class BusinessTypesCreateComponent extends Component
{
    use LivewireAlert;

    public ?string $name_en = null;
    public ?string $name_ar = null;
    public bool $is_active = true;

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        return view('livewire.pages.advertisers.business-types.create');
    }

    /**
     * @return null|void
     */
    public function store()
    {
        if (!Auth::guard('admin')->user()->can('business.types.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'name_en' => ['required', "unique:advertisers_business_types,name_en"],
            'name_ar' => ['required', "unique:advertisers_business_types,name_ar"],
            'is_active' => ['required', "boolean"],
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
                'is_active' => $this->is_active,
            ];
            AdvertiserBusinessType::create($data);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'name_en',
                'name_ar',
                'is_active',
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
