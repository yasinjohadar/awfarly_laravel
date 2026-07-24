<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Helpers\Filter;
use App\Models\Subscriptions\Packages\Package;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class PackagesShowComponent extends Component
{
    use LivewireAlert;

    public int $package_id;
    public bool $showEditModal = false;
    public ?string $product_id = null;
    public ?string $name_en = null;
    public ?string $name_ar = null;
    public ?int $maximum_posts = null;
    public ?string $description_en = null;
    public ?string $description_ar = null;
    public ?string $specifications_en = null;
    public ?string $specifications_ar = null;
    public ?float $price = null;
    public ?float $old_price = null;
    public string $subscription_type = 'monthly';
    public string $currency = 'SAR';
    public $is_visible = true;
    public $is_active = true;
    public $is_trial = false;

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get package
        $package = Package::where('id', $this->package_id)
            ->first();

        $package['current_price'] = "$package->price $package->currency / " . __("pages/subscriptions/packages/show.content.duration_types.$package->subscription_type");
        $package['full_price'] = "$package->old_price $package->currency / " . __("pages/subscriptions/packages/show.content.duration_types.$package->subscription_type");
        return view('admin.pages.subscriptions.packages.show', [
            'package' => $package,
            'showEditModal' => $this->showEditModal,
        ]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get package
        $package = Package::where('id', $this->package_id)
            ->first();

        $this->product_id = $package->product_id;
        $this->name_en = $package->name_en;
        $this->name_ar = $package->name_ar;
        $this->description_en = $package->description_en;
        $this->description_ar = $package->description_ar;
        $this->specifications_en = implode("\n", $package->specifications_en);
        $this->specifications_ar = implode("\n", $package->specifications_ar);
        $this->maximum_posts = $package->maximum_posts;
        $this->price = $package->price;
        $this->old_price = $package->old_price;
        $this->subscription_type = $package->subscription_type;
        $this->currency = $package->currency;
        $this->is_visible = $package->is_visible ? 1 : 0;
        $this->is_active = $package->is_active ? 1 : 0;
        $this->is_trial = $package->is_trial ? 1 : 0;

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;


        //reset validation messages
        $this->resetValidation();
    }

    public function update($id)
    {
        $this->validate([
            'product_id' => ['nullable', 'string', "unique:packages,product_id,$this->package_id"],
            'name_en' => ['required', 'string', "unique:packages,name_en,$this->package_id"],
            'name_ar' => ['required', 'string', "unique:packages,name_ar,$this->package_id"],
            'maximum_posts' => ['required', 'integer'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'specifications_en' => ['required', 'string'],
            'specifications_ar' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'old_price' => ['required', 'numeric'],
            'subscription_type' => ['required', 'in:daily,weekly,monthly,two_months,three_months,six_months,yearly'],
            'currency' => ['required', 'in:SAR'],
            'is_visible' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_trial' => ['required', 'boolean'],
        ]);

        $package = Package::where('id', $id)
            ->first();

        DB::beginTransaction();
        try {

            //close modal
            $this->closeEditModal();

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
            $package->update([
                'product_id' => $this->product_id ? Filter::RemoveHtml($this->product_id) : null,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
                'maximum_posts' => $this->maximum_posts,
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

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
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
        DB::commit();
    }

}
