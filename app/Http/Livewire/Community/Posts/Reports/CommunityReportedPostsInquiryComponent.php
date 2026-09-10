<?php

namespace App\Http\Livewire\Community\Posts\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CommunityReportedPostsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $model = Report::class;
    public $afterTableSlot = 'modals.community.posts.reports.delete-post';
    public string $status = 'all';
    public string $afterTableSlot2 = '';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public bool $has_delete = true;
    public bool $showDeleteModal = false;
    public array $deleteModalTexts;
    public bool $showDeletePostModal = false;
    public ?int $deletePostId = null;
    public array $deletePostModalTexts;

    /**
     * @var array
     */
    public $listeners = ['rerenderDataTable' => 'changeType'];

    /**
     * AdvertisersInquiryComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        //set modal texts
        $this->setModalTexts();

        parent::__construct($id);
    }

    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::checkbox('reported_id'),
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            NumberColumn::name('reported_id')
                ->label(__('pages/community/posts/reports/reports.content.datatable.post_id'))
                ->filterable()
                ->searchable()
            ->linkTo('admin/community/posts'),
            Column::callback(['reported_id'], function ($reported_id) {
                $post = Post::withTrashed()->find($reported_id);
                return Str::limit($post->content ?? '-', 40);
            }, ['post_content'])
                ->label(__('pages/community/posts/reports/reports.content.datatable.post_content'))
                ->unsortable(),
            NumberColumn::callback(['id', 'user_type'], function ($id, $user_type) {
                return Report::findOrFail($id)
                    ->reports_count;
            })
                ->label(__('pages/community/posts/reports/reports.content.datatable.reports_count'))
                ->searchable(),
            Column::callback(['reported_id'], function ($reported_id) {
                $latest = Report::where('reported_type', Post::class)
                    ->where('reported_id', $reported_id)
                    ->latest()
                    ->first();

                if (!$latest) {
                    return '-';
                }

                $type = __('pages/community/posts/reports/show.content.datatable.types.' . $latest->type);
                $reason = Str::limit($latest->reason ?? '-', 30);

                return "{$type} — {$reason}";
            }, ['latest_reason'])
                ->label(__('pages/community/posts/reports/reports.content.datatable.latest_reason'))
                ->unsortable(),
            Column::callback(['reported_id'], function ($reported_id) {
                $latest = Report::where('reported_type', Post::class)
                    ->where('reported_id', $reported_id)
                    ->latest()
                    ->first();

                return $latest?->created_at?->format('Y-m-d H:i') ?? '-';
            }, ['latest_report_date'])
                ->label(__('datatable.created_at'))
                ->unsortable(),
            Column::callback(['reported_id'], function ($reported_id) {
                return view('admin.pages.community.posts.reports.table-actions', ['reported_id' => $reported_id]);
            })
                ->label(__('datatable.actions'))
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        if ($this->status === 'all') {
            return Report::where('reported_type', Post::class)
                ->groupBy('reported_id')
                ->distinct('reported_id');
        }
        return Report::where('reported_type', Post::class)
            ->where('status', $this->status)
            ->groupBy('reported_id')
            ->distinct('reported_id');
    }

    /**
     * @param $params
     */
    public function changeType($params)
    {
        $this->status = $params['status'];
    }

    /**
     * show delete modal for selected rows, or a single reported post by id
     * @param int|null $id
     */
    public function showDeleteModal($id = null)
    {
        if ($id !== null) {
            $this->selected = [(string) $id];
        }

        if (empty($this->selected)) {
            return;
        }

        $this->deleteModalTexts = [
            'title' => __('pages/community/posts/reports/reports.modal.delete.title'),
            'content' => count($this->selected) === 1
                ? __('pages/community/posts/reports/reports.modal.delete.content_single')
                : __('pages/community/posts/reports/reports.modal.delete.content'),
            'cancel' => __('pages/community/posts/reports/reports.modal.delete.cancel'),
            'submit' => __('pages/community/posts/reports/reports.modal.delete.submit'),
        ];
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('posts.reported')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        if (empty($this->selected)) {
            $this->showDeleteModal = false;
            return null;
        }

        DB::beginTransaction();
        try {
            $reports = Report::whereIn('reported_id', $this->selected)
                ->where('reported_type', Post::class)
                ->get();

            Report::whereIn('reported_id', $this->selected)
                ->where('reported_type', Post::class)
                ->delete();

            $this->selected = [];

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            AdminLogs::log('delete', 'reports', [
                'reports' => $reports,
            ], 'Delete: reports');

            $this->showDeleteModal = false;
            $this->emitUp('recountCounters');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
    }

    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/community/posts/reports/reports.modal.delete.title'),
            'content' => __('pages/community/posts/reports/reports.modal.delete.content'),
            'cancel' => __('pages/community/posts/reports/reports.modal.delete.cancel'),
            'submit' => __('pages/community/posts/reports/reports.modal.delete.submit'),
        ];

        $this->deletePostModalTexts = [
            'title' => __('pages/community/posts/reports/reports.modal.delete_post.title'),
            'content' => __('pages/community/posts/reports/reports.modal.delete_post.content'),
            'cancel' => __('pages/community/posts/reports/reports.modal.delete_post.cancel'),
            'submit' => __('pages/community/posts/reports/reports.modal.delete_post.submit'),
        ];
    }

    /**
     * show the confirmation modal for deleting the post itself (not just its reports)
     * @param int $id
     */
    public function showDeletePostModal($id)
    {
        $this->deletePostId = $id;
        $this->showDeletePostModal = true;
    }

    public function closeDeletePostModal()
    {
        $this->showDeletePostModal = false;
        $this->deletePostId = null;
    }

    /**
     * delete the reported post itself (soft delete) and mark its reports as solved.
     * This does NOT delete the report records — use deleteSelected() for that.
     */
    public function deletePost()
    {
        if (!Auth::guard('admin')->user()->can('posts.delete')) {
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        if (!$this->deletePostId) {
            $this->showDeletePostModal = false;
            return null;
        }

        DB::beginTransaction();
        try {
            $post = Post::withTrashed()->findOrFail($this->deletePostId);

            $post->delete();

            Report::where('reported_type', Post::class)
                ->where('reported_id', $this->deletePostId)
                ->update([
                    'status' => 'solved',
                ]);

            AdminLogs::log('delete', 'posts', [
                'post' => $post,
            ], "Delete: post #$this->deletePostId");

            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->showDeletePostModal = false;
            $this->deletePostId = null;

            $this->emitUp('recountCounters');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
    }
}
