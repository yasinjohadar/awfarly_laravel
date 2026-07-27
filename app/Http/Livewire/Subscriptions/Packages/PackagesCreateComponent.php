<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Filter;
use App\Models\Subscriptions\Packages\Package;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class PackagesCreateComponent extends Component
{
    use LivewireAlert;

    public ?string $product_id = null;
    public ?string $name_en = null;
    public ?string $name_ar = null;
    public ?int $maximum_posts = null;
    public ?int $maximum_offers = null;
    public ?int $maximum_monthly_offers = null;
    public ?string $description_en = null;
    public ?string $description_ar = null;
    public ?string $specifications_en = null;
    public ?string $specifications_ar = null;
    public ?float $price = null;
    public ?float $old_price = null;
    public string $subscription_type = 'monthly';
    /*public ?int $duration = null;*/
    public string $currency = 'SAR';
    public int $is_visible = 1;
    public int $is_active = 1;
    public int $is_trial = 0;

    protected array $rules = [
        'product_id' => ['nullable', 'string', 'unique:packages,product_id'],
        'name_en' => ['required', 'string', 'unique:packages,name_en'],
        'name_ar' => ['required', 'string', 'unique:packages,name_ar'],
        'maximum_posts' => ['required', 'integer'],
        'maximum_offers' => ['required', 'integer', 'min:0'],
        'maximum_monthly_offers' => ['required', 'integer', 'min:0'],
        'description_en' => ['nullable', 'string'],
        'description_ar' => ['nullable', 'string'],
        'specifications_en' => ['required', 'string'],
        'specifications_ar' => ['required', 'string'],
        'price' => ['required', 'numeric'],
        'old_price' => ['required', 'numeric'],
        /*'duration' => ['required', 'int'],*/
        'subscription_type' => ['required', 'in:daily,weekly,monthly,two_months,three_months,six_months,yearly'],
        'currency' => ['required', 'in:SAR'], //usd too
        'is_visible' => ['required', 'boolean'],
        'is_active' => ['required', 'boolean'],
        'is_trial' => ['required', 'boolean'],
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
            $description_en = $this->description_en ? Filter::RemoveHtml($this->description_en) : null;
            if ($description_en) {
                $description_en = preg_replace("/[\r\n]+/", "\n", $description_en);
            }
            $description_ar = $this->description_ar ? Filter::RemoveHtml($this->description_ar) : null;
            if ($description_ar) {
                $description_ar = preg_replace("/[\r\n]+/", "\n", $description_ar);
            }

            $specifications_en = Filter::RemoveHtml($this->specifications_en);
            $specifications_en = preg_replace("/[\r\n]+/", "\n", $specifications_en);
            $specifications_en = explode("\n", $specifications_en);

            //send toastr alert with error
            $specifications_ar = Filter::RemoveHtml($this->specifications_ar);
            $specifications_ar = preg_replace("/[\r\n]+/", "\n", $specifications_ar);
            $specifications_ar = explode("\n", $specifications_ar);

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
                'product_id' => $this->product_id ? Filter::RemoveHtml($this->product_id) : null,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
                'maximum_posts' => $this->maximum_posts,
                'maximum_offers' => $this->maximum_offers,
                'maximum_monthly_offers' => $this->maximum_monthly_offers,
                'description_en' => $description_en,
                'description_ar' => $description_ar,
                'specifications_en' => $specifications_en,
                'specifications_ar' => $specifications_ar,
                'price' => $this->price,
                'old_price' => $this->old_price,
                'duration' => $duration,
                'subscription_type' => $this->subscription_type,
                'currency' => $this->currency,
                'is_visible' => $this->is_visible,
                'is_active' => $this->is_active,
                'is_trial' => $this->is_trial,
            ]);

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('add', 'packages', null, "Add: package");

            $this->resetValidation();
            $this->reset([
                'product_id',
                'name_en',
                'name_ar',
                'maximum_posts',
                'maximum_offers',
                'maximum_monthly_offers',
                'description_en',
                'description_ar',
                'specifications_en',
                'specifications_ar',
                'price',
                'old_price',
                /*'duration',*/
                'subscription_type',
                'currency',
                'is_visible',
                'is_active',
                'is_trial',
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
