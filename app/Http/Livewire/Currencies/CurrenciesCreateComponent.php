<?php

namespace App\Http\Livewire\Currencies;

use App\Helpers\Filter;
use App\Models\Currencies\Currency;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class CurrenciesCreateComponent extends Component
{
    use LivewireAlert;

    public string $code = '';
    public string $name_en = '';
    public string $name_ar = '';
    public ?string $symbol = null;
    public float $exchange_rate = 1;
    public $is_active = 1;
    public $is_visible = 1;

    protected array $rules = [
        'code' => ['required', 'unique:currencies,code'],
        'name_en' => ['required'],
        'name_ar' => ['required'],
        'symbol' => ['nullable'],
        'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
        'is_active' => ['boolean'],
        'is_visible' => ['boolean'],
    ];

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        return view('livewire.pages.currencies.create');
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('currencies.add')) {
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
                'symbol' => $this->symbol ? Filter::RemoveHtml($this->symbol) : null,
                'exchange_rate' => $this->exchange_rate,
                'is_base' => false,
                'is_active' => $this->is_active,
                'is_visible' => $this->is_visible,
                'order' => (Currency::max('order') ?? 0) + 1,
            ];
            Currency::create($data);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'code',
                'name_en',
                'name_ar',
                'symbol',
                'exchange_rate',
                'is_active',
                'is_visible',
            ]);
            $this->exchange_rate = 1;
            $this->is_active = 1;
            $this->is_visible = 1;
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
