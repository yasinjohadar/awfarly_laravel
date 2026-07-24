<?php

namespace App\Http\Livewire\Advertisers\Ratings;

use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Advertisers\Ratings\AdvertiserRatings;
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

class AdvertisersRatingsShowComponent extends Component
{
    use LivewireAlert;

    public int $rating_id;
    public bool $showEditModal = false;
    public ?string $status = null;
    public ?string $comment = null;
    public ?float $rate = null;

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get post
        $rating = AdvertiserRatings::with('user', 'advertiser')
            ->where('id', $this->rating_id)
            ->first();

        $rating['created_at'] = isset($rating['created_at']) ? Carbon::make($rating['created_at'])->format('Y-m-d h:i A') : null;

        return view('admin.pages.advertisers.ratings.show', [
            'rating' => $rating
        ]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        $rating = AdvertiserRatings::with('user', 'advertiser')
            ->where('id', $this->rating_id)
            ->first();

        $this->status = $rating->status;
        $this->comment = $rating->comment;
        $this->rate = $rating->rate;

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
            'status' => ['required', 'in:approved,unapproved,pending',],
            'comment' => ['nullable',],
            'rate' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        $rating = AdvertiserRatings::where('id', $id)
            ->first();

        DB::beginTransaction();
        try {
            $rating->update([
                'status' => $this->status,
                'comment' => $this->comment,
                'rate' => $this->rate,
            ]);

            $advertiser_rating = AdvertiserRatings::where('advertiser_id', $rating->advertiser_id)
                ->where('status', 'approved')
                ->avg('rate');

            $advertiser = AdvertiserUser::where('id', $rating->advertiser_id)
                ->update([
                    'rate' => $advertiser_rating
                ]);

            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->reset(['status', 'comment', 'rate']);

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
