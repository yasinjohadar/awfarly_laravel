<?php

namespace App\Http\Livewire\Community\Posts\Reports;

use App\Helpers\Admins\AdminLogs;
use App\Models\Posts\Post;
use App\Models\Reports\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
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
    public $afterTableSlot = '';
    public string $status = 'all';
    public string $afterTableSlot2 = '';
    public $beforeTableSlot = 'livewire.datatables.selected';
    public bool $has_delete = true;
    public bool $showDeleteModal = false;
    public array $deleteModalTexts;

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
            NumberColumn::callback(['id', 'user_type'], function ($id, $user_type) {
                return Report::findOrFail($id)
                    ->reports_count;
            })
                ->label(__('pages/community/posts/reports/reports.content.datatable.reports_count'))
                ->searchable(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
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
    }
}
