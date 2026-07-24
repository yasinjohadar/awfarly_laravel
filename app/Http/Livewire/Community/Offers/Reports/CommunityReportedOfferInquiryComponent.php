<?php

namespace App\Http\Livewire\Community\Offers\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Http\Resources\Media\MediaResource;
use App\Models\Offers\Offer;
use App\Models\Reports\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class CommunityReportedOfferInquiryComponent extends Component
{
    use LivewireAlert;

    public int $offer_id;
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
        $offer = Offer::withTrashed()
            ->where('id', $this->offer_id)
            ->first();

        $offer['created_at'] = isset($offer['created_at']) ? Carbon::make($offer['created_at'])->format('Y-m-d h:i A') : null;
        $offer['media'] = MediaResource::collection($offer->getMedia('offers'))->resolve();
        return view('livewire.pages.community.offers.reports.show', ['offer' => $offer]);
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
        if (!Auth::guard('admin')->user()->can('offers.reported')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $offer = Offer::withTrashed()
            ->where('id', $this->offer_id)
            ->first();

        DB::beginTransaction();
        try {
            $offer->delete();

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'offers', [
                'offer' => $offer
            ], "Delete: offer #$this->offer_id");

            Report::where('reported_type', Offer::class)
                ->where('reported_id', $this->offer_id)
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
        if (!Auth::guard('admin')->user()->can('offers.reported')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $offer = Offer::withTrashed()
            ->where('id', $this->offer_id)
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
                'offer' => $offer
            ], "Solve: offer #$this->offer_id");

            Report::where('reported_type', Offer::class)
                ->where('reported_id', $this->offer_id)
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
            'title' => __('pages/community/offers/reports/show.modal.solve.title'),
            'content' => __('pages/community/offers/reports/show.modal.solve.content'),
            'cancel' => __('pages/community/offers/reports/show.modal.solve.cancel'),
            'submit' => __('pages/community/offers/reports/show.modal.solve.submit'),
        ];

        $this->deleteModalTexts = [
            'title' => __('pages/community/comments/reports/show.modal.delete.title'),
            'content' => __('pages/community/comments/reports/show.modal.delete.content'),
            'cancel' => __('pages/community/comments/reports/show.modal.delete.cancel'),
            'submit' => __('pages/community/comments/reports/show.modal.delete.submit'),
        ];
    }
}
