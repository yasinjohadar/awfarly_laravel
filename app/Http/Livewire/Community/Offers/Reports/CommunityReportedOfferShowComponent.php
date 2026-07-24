<?php

namespace App\Http\Livewire\Community\Offers\Reports;

use App\Models\Offers\Offer;
use App\Models\Reports\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Illuminate\Support\Facades\Auth;

class CommunityReportedOfferShowComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public int $offer_id;
    public $beforeTableSlot = 'modals.community.offers.reports.show';
    public $hideable = 'select';
    public $afterTableSlot = '';
    public string $afterTableSlot2 = '';
    public array $showMoreModalTexts;
    public bool $showMoreModal = false;
    public array $log;

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
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            NumberColumn::name('reported_id')
                ->label(__('pages/community/offers/reports/show.content.datatable.offer_id'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'created_at'], function ($id) {
                $user_type = Report::findOrFail($id);
                $user_type = $user_type->user_id ? $user_type->user->user_type : __('pages/community/comments/reports/show.content.datatable.guest');
                return ucwords($user_type);
            })
                ->label(__('pages/community/offers/reports/show.content.datatable.user_type'))
                ->searchable()
                ->unsortable(),
            NumberColumn::name('user_id')
                ->label(__('pages/community/offers/reports/show.content.datatable.user_id'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id'], function ($id) {
                $username = Report::findOrFail($id);
                $username = $username->user_id ? $username->user->name : __('pages/community/comments/reports/show.content.datatable.guest');
                return ucwords($username);
            })
                ->label(__('pages/community/offers/reports/show.content.datatable.user_name'))
                ->searchable(),
            Column::callback('type', function ($type) {
                return $type ? __("pages/community/offers/reports/show.content.datatable.types.{$type}") : '-';
            })
                ->label(__('pages/community/offers/reports/show.content.datatable.type'))
                ->filterable([
                    'Violence' => __("pages/community/offers/reports/show.content.datatable.types.Violence"),
                    'Sexually Inappropriate' => __("pages/community/offers/reports/show.content.datatable.types.Sexually Inappropriate"),
                    'Abusive Content' => __("pages/community/offers/reports/show.content.datatable.types.Abusive Content"),
                    'Misleading or Scam' => __("pages/community/offers/reports/show.content.datatable.types.Misleading or Scam"),
                    'Offensive' => __("pages/community/offers/reports/show.content.datatable.types.Offensive"),
                    'Prohibited Content' => __("pages/community/offers/reports/show.content.datatable.types.Prohibited Content"),
                    'Spam' => __("pages/community/offers/reports/show.content.datatable.types.Spam"),
                ])
                ->searchable(),
            Column::callback('reason', function ($reason) {
                return $reason ? Str::limit($reason, 30) : '-';
            })
                ->label(__('pages/community/offers/reports/show.content.datatable.reason'))
                ->filterable()
                ->searchable()
                ->hide(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
            Column::callback(['id', 'updated_at'], function ($id, $name) {
                return view('admin.pages.community.offers.reports.show-table-actions', ['id' => $id, 'name' => $name]);
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
        return Report::where('reported_type', Offer::class)
            ->where('reported_id', $this->offer_id);
    }

    /**
     * set modal texts
     */
    public function setModalTexts()
    {
        $this->showMoreModalTexts = [
            'title' => __('pages/community/offers/reports/show.modal.show.title'),
            'close' => __('pages/community/offers/reports/show.modal.show.close'),
        ];
    }

    /**
     * show delete modal
     */
    public function showMoreModal($id)
    {
        //check if user is allowed to do this action or not
        if (!Auth::guard('admin')->user()->can('offers.reported')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        //get the report
        $report = Report::with('user')
            ->where('reported_type', Offer::class)
            ->where('id', $id)
            ->first();

        //set log
        $this->log = [
            'offer_id' => $report->reported_id,
            'type' => $report->type,
            'user_type' => $report->user ? ucwords($report->user->user_type) : __('pages/community/comments/reports/show.content.datatable.guest'),
            'user_id' => $report->user ? $report->user->id : __('pages/community/comments/reports/show.content.datatable.guest'),
            'user_name' => $report->user ? $report->user->name : __('pages/community/comments/reports/show.content.datatable.guest'),
            'reason' => $report->reason ?? '-',
            'created_at' => Carbon::make($report->created_at)->format('Y-m-d h:i A'),
        ];

        //set show more to true
        $this->showMoreModal = true;
    }

    /**
     * show delete modal
     */
    public function closeShowMoreModal()
    {
        $this->showMoreModal = false;
        $this->log = [];
        //reset validation messages
        $this->resetValidation();
    }
}
