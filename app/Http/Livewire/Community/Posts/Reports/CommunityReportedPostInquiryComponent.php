<?php

namespace App\Http\Livewire\Community\Posts\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Http\Resources\Media\MediaResource;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Throwable;

class CommunityReportedPostInquiryComponent extends Component
{
    use LivewireAlert;

    public int $post_id;
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
        $post = Post::withTrashed()
            ->where('id', $this->post_id)
            ->first();

        $post['created_at'] = isset($post['created_at']) ? Carbon::make($post['created_at'])->format('Y-m-d h:i A') : null;
        $post['media'] = MediaResource::collection($post->getMedia('posts'))->resolve();

        //get report status
        $report_status = optional($post->reports()->first())->status ?? 'pending';
        $reports_count = $post->reports()->count();

        return view('livewire.pages.community.posts.reports.show', [
            'post' => $post,
            'status' => $report_status,
            'reports_count' => $reports_count,
        ]);
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
        $post = Post::withTrashed()
            ->where('id', $this->post_id)
            ->first();

        DB::beginTransaction();
        try {
            $post->delete();

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //hide modal
            $this->showDeleteModal = false;

            //add log
            AdminLogs::log('delete', 'posts', [
                'post' => $post
            ], "Delete: post #$this->post_id");

            Report::where('reported_type', Post::class)
                ->where('reported_id', $this->post_id)
                ->update([
                    'status' => 'solved'
                ]);

            $this->emitUp('recountCounters');
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
        if (!Auth::guard('admin')->user()->can('posts.reported')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $post = Post::withTrashed()
            ->where('id', $this->post_id)
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
                'post' => $post
            ], "Solve: post #$this->post_id");

            Report::where('reported_type', Post::class)
                ->where('reported_id', $this->post_id)
                ->update([
                    'status' => 'solved'
                ]);

            $this->emitUp('recountCounters');
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
            'title' => __('pages/community/posts/reports/show.modal.solve.title'),
            'content' => __('pages/community/posts/reports/show.modal.solve.content'),
            'cancel' => __('pages/community/posts/reports/show.modal.solve.cancel'),
            'submit' => __('pages/community/posts/reports/show.modal.solve.submit'),
        ];

        $this->deleteModalTexts = [
            'title' => __('pages/community/posts/reports/show.modal.delete.title'),
            'content' => __('pages/community/posts/reports/show.modal.delete.content'),
            'cancel' => __('pages/community/posts/reports/show.modal.delete.cancel'),
            'submit' => __('pages/community/posts/reports/show.modal.delete.submit'),
        ];
    }
}
