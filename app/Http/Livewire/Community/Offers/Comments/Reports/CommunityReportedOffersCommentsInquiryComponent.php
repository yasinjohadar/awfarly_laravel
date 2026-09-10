<?php

namespace App\Http\Livewire\Community\Offers\Comments\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Reports\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Throwable;

class CommunityReportedOffersCommentsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public $model = Report::class;
    public $afterTableSlot = 'modals.community.offers.comments.reports.delete-post';
    public string $status = 'all';
    public string $afterTableSlot2 = '';
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
                ->label(__('pages/community/offers/comments/reports/reports.content.datatable.comment_id'))
                ->filterable()
                ->searchable()
                ->linkTo('admin/community/offers/comments'),
            Column::callback(['reported_id'], function ($reported_id) {
                $comment = OffersComments::withTrashed()->find($reported_id);
                return Str::limit($comment->comment ?? '-', 40);
            }, ['comment_content'])
                ->label(__('pages/community/offers/comments/reports/reports.content.datatable.comment_content'))
                ->unsortable(),
            NumberColumn::callback(['id', 'user_type'], function ($id, $user_type) {
                return Report::findOrFail($id)
                    ->reports_count;
            })
                ->label(__('pages/community/offers/comments/reports/reports.content.datatable.reports_count'))
                ->searchable(),
            Column::callback(['reported_id'], function ($reported_id) {
                $latest = Report::where('reported_type', OffersComments::class)
                    ->where('reported_id', $reported_id)
                    ->latest()
                    ->first();

                if (!$latest) {
                    return '-';
                }

                $type = __('pages/community/offers/comments/reports/show.content.datatable.types.' . $latest->type);
                $reason = Str::limit($latest->reason ?? '-', 30);

                return "{$type} — {$reason}";
            }, ['latest_reason'])
                ->label(__('pages/community/offers/comments/reports/reports.content.datatable.latest_reason'))
                ->unsortable(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['reported_id'], function ($reported_id) {
                return view('admin.pages.community.offers.comments.reports.table-actions', ['reported_id' => $reported_id]);
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
            return Report::where('reported_type', OffersComments::class)
                ->groupBy('reported_id')
                ->distinct('reported_id');
        }

        return Report::where('reported_type', OffersComments::class)
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
     * show delete modal
     * @param $id
     */
    public function showDeleteModal($id = null)
    {
        if ($id) {
            $this->selected = [$id];
        }
        $this->showDeleteModal = true;
    }

    /**
     * delete Selected data
     */
    public function deleteSelected()
    {
        if (!Auth::guard('admin')->user()->can('comments.delete')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        DB::beginTransaction();
        try {
            //get categories
            $reports = Report::whereIn('reported_id', $this->selected)
                ->where('reported_type', OffersComments::class)
                ->get();

            //set selected data to null
            $this->selected = [];

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            //add log
            AdminLogs::log('delete', 'reports', [
                'reports' => $reports
            ], "Delete: reports");

            foreach ($reports as $report) {
                $report->delete();
            }
            //close modal
            $this->showDeleteModal = false;

            $this->emitUp('recountCounters');
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
        //commit
        DB::commit();
    }

    public function setModalTexts()
    {
        $this->deleteModalTexts = [
            'title' => __('pages/community/offers/comments/reports/reports.modal.delete.title'),
            'content' => __('pages/community/offers/comments/reports/reports.modal.delete.content'),
            'cancel' => __('pages/community/offers/comments/reports/reports.modal.delete.cancel'),
            'submit' => __('pages/community/offers/comments/reports/reports.modal.delete.submit'),
        ];
        $this->deletePostModalTexts = [
            'title' => __('pages/community/offers/comments/reports/reports.modal.delete_post.title'),
            'content' => __('pages/community/offers/comments/reports/reports.modal.delete_post.content'),
            'cancel' => __('pages/community/offers/comments/reports/reports.modal.delete_post.cancel'),
            'submit' => __('pages/community/offers/comments/reports/reports.modal.delete_post.submit'),
        ];
    }

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

    public function deletePost()
    {
        if (!Auth::guard('admin')->user()->can('comments.delete')) {
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
            $comment = OffersComments::withTrashed()->findOrFail($this->deletePostId);

            $comment->delete();

            Report::where('reported_type', OffersComments::class)
                ->where('reported_id', $this->deletePostId)
                ->update([
                    'status' => 'solved',
                ]);

            AdminLogs::log('delete', 'comments', [
                'comment' => $comment,
            ], "Delete: comment #$this->deletePostId");

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
