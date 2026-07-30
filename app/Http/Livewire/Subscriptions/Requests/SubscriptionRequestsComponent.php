<?php

namespace App\Http\Livewire\Subscriptions\Requests;

use App\Helpers\Advertisers\PackageQuotas;
use App\Helpers\Admins\AdminLogs;
use App\Models\Subscriptions\Packages\PackageSubscriptionRequest;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionRequestsComponent extends Component
{
    use LivewireAlert;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public string $page_type = 'pending';
    public ?string $admin_notes = null;

    public function updatingPageType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = PackageSubscriptionRequest::with(['advertiser', 'package', 'reviewer'])
            ->latest();

        if ($this->page_type !== 'all') {
            $query->where('status', $this->page_type);
        }

        return view('livewire.pages.subscriptions.requests.index', [
            'requests' => $query->paginate(20),
            'pending_count' => PackageSubscriptionRequest::where('status', 'pending')->count(),
            'approved_count' => PackageSubscriptionRequest::where('status', 'approved')->count(),
            'rejected_count' => PackageSubscriptionRequest::where('status', 'rejected')->count(),
            'all_count' => PackageSubscriptionRequest::count(),
        ]);
    }

    public function changeActiveTab($active)
    {
        $this->page_type = $active;
    }

    public function approve($id)
    {
        if (!Auth::guard('admin')->user()->can('payments.inquiry')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            $request = PackageSubscriptionRequest::with(['advertiser', 'package'])
                ->where('id', $id)
                ->where('status', 'pending')
                ->firstOrFail();

            PackageQuotas::assignPackage($request->advertiser, $request->package);

            $request->update([
                'status' => 'approved',
                'notes' => $this->admin_notes,
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            AdminLogs::log('edit', 'subscriptions', [
                'request_id' => $request->id,
                'status' => 'approved',
            ], "Approve package subscription request #{$request->id}");

            DB::commit();
            $this->admin_notes = null;

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function reject($id)
    {
        if (!Auth::guard('admin')->user()->can('payments.inquiry')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            $request = PackageSubscriptionRequest::where('id', $id)
                ->where('status', 'pending')
                ->firstOrFail();

            $request->update([
                'status' => 'rejected',
                'notes' => $this->admin_notes,
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            AdminLogs::log('edit', 'subscriptions', [
                'request_id' => $request->id,
                'status' => 'rejected',
            ], "Reject package subscription request #{$request->id}");

            DB::commit();
            $this->admin_notes = null;

            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
        }
    }
}
