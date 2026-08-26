<?php

namespace App\Http\Livewire\Subscriptions\Packages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Advertisers\PackageQuotas;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Users\Advertisers\AdvertiserUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PackagesShowComponent extends Component
{
    use WithPagination;
    use LivewireAlert;

    protected $paginationTheme = 'bootstrap';

    public int $package_id;
    public string $features_lang = 'ar';

    public Collection $advertisers;

    public bool $showAssignAdvertiserModal = false;
    public ?string $assign_advertiser_id = null;

    public bool $showEndSubscriptionModal = false;
    public ?int $end_subscription_id = null;
    public ?string $end_subscription_advertiser_name = null;

    public function mount(): void
    {
        $this->features_lang = App::currentLocale() === 'en' ? 'en' : 'ar';

        //kept as a simple in-memory list for the assign-advertiser select2, matching
        //the same pattern already used for the package dropdown in the advertisers
        //edit modal — fine at this advertiser-count scale
        $this->advertisers = AdvertiserUser::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn ($advertiser) => [
                'id' => $advertiser->id,
                'name' => "{$advertiser->name} (#{$advertiser->id} - {$advertiser->username})",
            ]);
    }

    public function setFeaturesLang(string $lang): void
    {
        if (!in_array($lang, ['ar', 'en'], true)) {
            return;
        }

        $this->features_lang = $lang;
    }

    public function render()
    {
        $package = Package::where('id', $this->package_id)
            ->withCount('advertisers')
            ->first();

        //advertisers currently/previously subscribed to this package — current
        //subscriptions first, then most recently started
        $subscriptions = AdvertiserPackages::where('package_id', $this->package_id)
            ->with('advertiser')
            ->orderByDesc('is_current')
            ->orderByDesc('starts_at')
            ->paginate(10);

        return view('admin.pages.subscriptions.packages.show', [
            'package' => $package,
            'features_lang' => $this->features_lang,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function openAssignAdvertiserModal(): void
    {
        $this->assign_advertiser_id = null;
        $this->showAssignAdvertiserModal = true;
    }

    public function closeAssignAdvertiserModal(): void
    {
        $this->showAssignAdvertiserModal = false;
        $this->assign_advertiser_id = null;
        $this->resetValidation();
    }

    /**
     * Assign this package to a chosen advertiser — the same
     * PackageQuotas::assignPackage() seam used everywhere else (admin advertiser
     * edit modal, IAP purchase, subscription-request approval), so quotas and
     * is_elite are applied consistently regardless of where the assignment
     * happened from.
     */
    public function assignAdvertiserToPackage(): void
    {
        if (!Auth::guard('admin')->user()->can('packages.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return;
        }

        $this->validate([
            'assign_advertiser_id' => ['required', 'exists:advertisers_users,id'],
        ]);

        DB::beginTransaction();
        try {
            $advertiser = AdvertiserUser::findOrFail($this->assign_advertiser_id);
            $package = Package::findOrFail($this->package_id);

            PackageQuotas::assignPackage($advertiser, $package);

            AdminLogs::log('edit', 'packages', [
                'package_id' => $this->package_id,
                'advertiser_id' => $advertiser->id,
            ], "Assign advertiser #{$advertiser->id} to package #{$this->package_id}");

            $this->closeAssignAdvertiserModal();
            $this->resetPage();

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return;
        }

        DB::commit();
    }

    public function openEndSubscriptionModal(int $subscriptionId): void
    {
        $subscription = AdvertiserPackages::with('advertiser')
            ->where('package_id', $this->package_id)
            ->findOrFail($subscriptionId);

        $this->end_subscription_id = $subscription->id;
        $this->end_subscription_advertiser_name = optional($subscription->advertiser)->name;
        $this->showEndSubscriptionModal = true;
    }

    public function closeEndSubscriptionModal(): void
    {
        $this->showEndSubscriptionModal = false;
        $this->end_subscription_id = null;
        $this->end_subscription_advertiser_name = null;
    }

    /**
     * End one advertiser's subscription to this package. Mirrors what
     * `check:subscriptions-timers` does on natural expiry: mark the row ended,
     * then let PackageQuotas re-sync the advertiser (free tier, or whatever
     * other active package they still have).
     */
    public function endSubscription(): void
    {
        if (!Auth::guard('admin')->user()->can('packages.edit')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return;
        }

        if (!$this->end_subscription_id) {
            $this->closeEndSubscriptionModal();
            return;
        }

        DB::beginTransaction();
        try {
            $subscription = AdvertiserPackages::where('package_id', $this->package_id)
                ->findOrFail($this->end_subscription_id);

            $advertiser = $subscription->advertiser;

            $subscription->update([
                'is_active' => false,
                'is_ended' => true,
                'is_current' => false,
                'ends_at' => now(),
            ]);

            if ($advertiser) {
                PackageQuotas::afterSubscriptionEnded($advertiser);
            }

            AdminLogs::log('edit', 'packages', [
                'package_id' => $this->package_id,
                'advertiser_id' => $advertiser?->id,
            ], "End subscription #{$subscription->id} on package #{$this->package_id}");

            $this->closeEndSubscriptionModal();

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return;
        }

        DB::commit();
    }
}
