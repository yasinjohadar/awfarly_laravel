<?php

namespace App\Http\Livewire\Advertisers\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Http\Resources\Media\MediaResource;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use App\Models\Users\Advertisers\AdvertiserUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class ReportedAdvertiserInquiryComponent extends Component
{
    use LivewireAlert;

    public int $advertiser_id;
    public bool $showSolveModal = false;
    public bool $showDeleteModal = false;
    public string $active;
    public array $solveModalTexts;
    public array $deleteModalTexts;

    public function __construct($id = null)
    {
        $this->setModalTexts();

        parent::__construct($id);
    }

    public function render()
    {
        $advertiser = AdvertiserUser::query()
            ->where('id', $this->advertiser_id)
            ->first();

        $advertiser['created_at'] = isset($advertiser['created_at']) ? Carbon::make($advertiser['created_at'])->format('Y-m-d h:i A') : null;

        //get report status
        $report_status = $advertiser->report()
            ->first()
            ->status;

        return view('livewire.pages.advertisers.reports.show', ['advertiser' => $advertiser, 'status' => $report_status]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showSolveModal($id)
    {
        //show the modal
        $this->showSolveModal = true;
    }

    public function closeSolveModal()
    {
        //close the modal
        $this->showSolveModal = false;
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showDeleteModal($id)
    {
        //show the modal
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        //close the modal
        $this->showDeleteModal = false;
    }

    public function delete()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('advertisers.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $advertiser = AdvertiserUser::query()
            ->where('id', $this->advertiser_id)
            ->first();

        DB::beginTransaction();
        try {
            $advertiser->update([
                'status' => 'banned'
            ]);

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('edit', 'advertisers', [
                'advertiser' => $advertiser
            ], "Ban: advertiser #$this->advertiser_id");

            Report::query()
                ->where('reported_type', AdvertiserUser::class)
                ->where('reported_id', $this->advertiser_id)
                ->update([
                    'status' => 'solved'
                ]);
        } catch (Throwable $e) {

            //rollback changes
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        //commit changes
        DB::commit();
    }


    public function solve()
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('advertisers.inquiry')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $advertiser = AdvertiserUser::query()
            ->where('id', $this->advertiser_id)
            ->first();

        DB::beginTransaction();
        try {
            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showSolveModal = false;

            //add log
            AdminLogs::log('decline', 'reports', [
                'advertiser' => $advertiser
            ], "Solve: advertiser #$this->advertiser_id");

            Report::where('reported_type', AdvertiserUser::class)
                ->where('reported_id', $this->advertiser_id)
                ->update([
                    'status' => 'solved'
                ]);
        } catch (Throwable $e) {

            //rollback changes
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        //commit changes
        DB::commit();
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->solveModalTexts = [
            'title' => __('pages/advertisers/reports/show.modal.solve.title'),
            'content' => __('pages/advertisers/reports/show.modal.solve.content'),
            'cancel' => __('pages/advertisers/reports/show.modal.solve.cancel'),
            'submit' => __('pages/advertisers/reports/show.modal.solve.submit'),
        ];

        $this->deleteModalTexts = [
            'title' => __('pages/advertisers/reports/show.modal.delete.title'),
            'content' => __('pages/advertisers/reports/show.modal.delete.content'),
            'cancel' => __('pages/advertisers/reports/show.modal.delete.cancel'),
            'submit' => __('pages/advertisers/reports/show.modal.delete.submit'),
        ];
    }
}
