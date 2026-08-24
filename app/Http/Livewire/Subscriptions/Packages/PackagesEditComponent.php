<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Advertisers\PackageQuotas;
use App\Helpers\Filter;
use App\Models\Currencies\Currency;
use App\Models\Subscriptions\Packages\Package;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class PackagesEditComponent extends Component
{
    use LivewireAlert;

    public int $packageId;

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
    public string $currency = 'SAR';
    public $is_visible = 1;
    public $is_active = 1;
    public $is_trial = 0;
    public $is_elite = 1;

    public function mount($id)
    {
        $package = Package::findOrFail($id);

        $this->packageId = $package->id;
        $this->product_id = $package->product_id;
        $this->name_en = $package->name_en;
        $this->name_ar = $package->name_ar;
        $this->description_en = $package->description_en;
        $this->description_ar = $package->description_ar;
        $this->specifications_en = implode("\n", $package->specifications_en ?? []);
        $this->specifications_ar = implode("\n", $package->specifications_ar ?? []);
        $this->maximum_posts = $package->maximum_posts;
        $this->maximum_offers = $package->maximum_offers;
        $this->maximum_monthly_offers = $package->maximum_monthly_offers;
        $this->price = $package->price;
        $this->old_price = $package->old_price;
        $this->subscription_type = $package->subscription_type;
        $this->currency = $package->currency;
        $this->is_visible = $package->is_visible ? 1 : 0;
        $this->is_active = $package->is_active ? 1 : 0;
        $this->is_trial = $package->is_trial ? 1 : 0;
        $this->is_elite = $package->is_elite ? 1 : 0;
    }

    protected function rules(): array
    {
        return [
            'product_id' => ['nullable', 'string', "unique:packages,product_id,{$this->packageId}"],
            'name_en' => ['required', 'string', "unique:packages,name_en,{$this->packageId}"],
            'name_ar' => ['required', 'string', "unique:packages,name_ar,{$this->packageId}"],
            'maximum_posts' => ['required', 'integer'],
            'maximum_offers' => ['required', 'integer', 'min:0'],
            'maximum_monthly_offers' => ['required', 'integer', 'min:0'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'specifications_en' => ['required', 'string'],
            'specifications_ar' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'old_price' => ['nullable', 'numeric'],
            'subscription_type' => ['required', 'in:daily,weekly,monthly,two_months,three_months,six_months,yearly'],
            'currency' => ['required', Rule::exists('currencies', 'code')->where('is_active', true)],
            'is_visible' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_trial' => ['required', 'boolean'],
            'is_elite' => ['required', 'boolean'],
        ];
    }

    public function render()
    {
        return view('livewire.pages.subscriptions.packages.edit', [
            'currencies' => Currency::where('is_active', true)->orderBy('order')->get(),
        ]);
    }

    public function update()
    {
        if (!Auth::guard('admin')->user()->can('packages.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate();

        $package = Package::findOrFail($this->packageId);

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

            $specifications_ar = Filter::RemoveHtml($this->specifications_ar);
            $specifications_ar = preg_replace("/[\r\n]+/", "\n", $specifications_ar);
            $specifications_ar = explode("\n", $specifications_ar);

            $duration = Package::durationDaysForType($this->subscription_type);

            $package->update([
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
                'is_elite' => $this->is_elite,
            ]);

            //without this, subscribers already on this package would only pick up
            //the new flags/quotas at their next renewal/expiry — apply them now
            PackageQuotas::resyncSubscribersOfPackage($package);

            //add log
            AdminLogs::log('edit', 'packages', ['package' => $package], "Edit: package");
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

        //flash toastr alert with success and redirect to the packages index
        return $this->flash('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ], route('admin.subscriptions.packages.index'));
    }
}
