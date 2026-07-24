<?php

namespace App\Http\Livewire\Advertisements\Comments\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Models\Posts\Comments\PostComments;
use App\Models\Reports\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class AdvertisementReportedCommentInquiryComponent extends Component
{
    use LivewireAlert;

    public int $comment_id;
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
        $comment = PostComments::withTrashed()
            ->whereHas('post', function ($q) {
                $q->whereNotNull('advertisement_id');
            })
            ->where('id', $this->comment_id)
            ->first();

        $comment['created_at'] = isset($comment['created_at']) ? Carbon::make($comment['created_at'])->format('Y-m-d h:i A') : null;

        return view('livewire.pages.advertisements.comments.reports.show', ['comment' => $comment]);
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
        if (!Auth::guard('admin')->user()->can('posts.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $comment = PostComments::withTrashed()
            ->where('id', $this->comment_id)
            ->first();

        DB::beginTransaction();
        try {
            $comment->delete();

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'comments', [
                'comment' => $comment
            ], "Delete: comment #$this->comment_id");

            Report::where('reported_type', PostComments::class)
                ->whereHasMorph('reported', [PostComments::class], function ($q) {
                    $q->whereHas('post', function ($query) {
                        $query->whereNotNull('advertisement_id');
                    });
                })
                ->where('reported_id', $this->comment_id)
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
        if (!Auth::guard('admin')->user()->can('comments.reported')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $comment = PostComments::withTrashed()
            ->whereHas('post', function ($q) {
                $q->whereNotNull('advertisement_id');
            })
            ->where('id', $this->comment_id)
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
                'comment' => $comment
            ], "Solve: comment #$this->comment_id");

            Report::where('reported_type', PostComments::class)
                ->whereHasMorph('reported', [PostComments::class], function ($q) {
                    $q->whereHas('post', function ($query) {
                        $query->whereNotNull('advertisement_id');
                    });
                })
                ->where('reported_id', $this->comment_id)
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
            'title' => __('pages/community/comments/reports/show.modal.solve.title'),
            'content' => __('pages/community/comments/reports/show.modal.solve.content'),
            'cancel' => __('pages/community/comments/reports/show.modal.solve.cancel'),
            'submit' => __('pages/community/comments/reports/show.modal.solve.submit'),
        ];

        $this->deleteModalTexts = [
            'title' => __('pages/community/comments/reports/show.modal.delete.title'),
            'content' => __('pages/community/comments/reports/show.modal.delete.content'),
            'cancel' => __('pages/community/comments/reports/show.modal.delete.cancel'),
            'submit' => __('pages/community/comments/reports/show.modal.delete.submit'),
        ];
    }
}
