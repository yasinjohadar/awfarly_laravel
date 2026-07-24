<?php

namespace App\Http\Livewire\Subscriptions\Payments;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Subscriptions\Packages\Package;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class PaymentsCreateComponent extends Component
{
    use LivewireAlert;

    public ?string $name = null;
    public ?int $maximum_posts = null;
    public ?string $description = null;
    public ?string $specifications = null;
    public ?int $price = null;
    public ?int $old_price = null;
    public string $subscription_type = 'monthly';
    public string $currency = 'SAR';
    public bool $is_visible = true;
    public bool $is_active = true;

    protected array $rules = [
        'name' => ['required', 'string', 'unique:packages,name'],
        'maximum_posts' => ['required', 'integer'],
        'description' => ['nullable', 'string'],
        'specifications' => ['required', 'string'],
        'price' => ['required', 'int'],
        'old_price' => ['required', 'int'],
        'subscription_type' => ['required', 'in:daily,weekly,monthly,two_months,three_months,six_months,yearly'],
        'currency' => ['required', 'in:SAR,USD'],
        'is_visible' => ['required', 'boolean'],
        'is_active' => ['required', 'boolean'],
    ];

    public function render()
    {
        return view('livewire.pages.subscriptions.packages.create');
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('packages.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate();
        DB::beginTransaction();
        try {
            $description = $this->description ? Filter::RemoveHtml($this->description) : null;
            if ($description) {
                $description = preg_replace("/[\r\n]+/", "\n", $description);
            }

            $specifications = Filter::RemoveHtml($this->specifications);
            $specifications = preg_replace("/[\r\n]+/", "\n", $specifications);
            $specifications = explode("\n", $specifications);
            if ($this->subscription_type === 'monthly') {
                $duration = 1;
            } else if ($this->subscription_type === 'two_months') {
                $duration = 2;
            } else if ($this->subscription_type === 'three_months') {
                $duration = 3;
            } else if ($this->subscription_type === 'six_months') {
                $duration = 6;
            } else if ($this->subscription_type === 'yearly') {
                $duration = 1;
            } else {
                $duration = 1;
            }
            Package::create([
                'name' => Filter::RemoveHtml($this->name),
                'maximum_posts' => $this->maximum_posts,
                'description' => $description,
                'specifications' => $specifications,
                'price' => $this->price,
                'old_price' => $this->old_price,
                'duration' => $duration,
                'subscription_type' => $this->subscription_type,
                'currency' => $this->currency,
                'is_visible' => $this->is_visible,
                'is_active' => $this->is_active,
            ]);


            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('add', 'packages', null, "Add: package");

            $this->resetValidation();
            $this->reset([
                'name',
                'maximum_posts',
                'description',
                'specifications',
                'price',
                'old_price',
                'subscription_type',
                'currency',
                'is_visible',
                'is_active',
            ]);
        } catch (Throwable $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        //commit
        DB::commit();
    }
}
