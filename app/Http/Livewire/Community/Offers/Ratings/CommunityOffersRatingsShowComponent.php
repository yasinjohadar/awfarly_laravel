<?php

namespace App\Http\Livewire\Community\Offers\Ratings;

use App\Models\Offers\Offer;
use App\Models\Offers\Ratings\OfferRatings;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class CommunityOffersRatingsShowComponent extends Component
{
    use LivewireAlert;

    public int $rating_id;
    public bool $showEditModal = false;
    public ?string $status = null;

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get post
        $rating = OfferRatings::with('user')
            ->where('id', $this->rating_id)
            ->first();

        $rating['created_at'] = isset($rating['created_at']) ? Carbon::make($rating['created_at'])->format('Y-m-d h:i A') : null;

        return view('admin.pages.community.offers.ratings.show', [
            'rating' => $rating
        ]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        $this->status = OfferRatings::where('id', $this->rating_id)
            ->first()
            ->status;

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
        if (!Auth::guard('admin')->user()->can('ratings.approve')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $this->validate([
            'status' => ['required', 'in:approved,unapproved,pending',]
        ]);

        $rating = OfferRatings::where('id', $id)
            ->first();

        DB::beginTransaction();
        try {
            $rating->update([
                'status' => $this->status,
            ]);

            $offer_rating = OfferRatings::where('offer_id', $rating->offer_id)
                ->where('status', 'approved')
                ->avg('rate');

            Offer::where('id', $rating->offer_id)
                ->update([
                    'rate' => $offer_rating
                ]);

            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset(['status']);

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
