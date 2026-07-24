<?php

namespace App\Http\Livewire\Community\Offers\Comments\Reports;

use App\Models\Offers\Comments\OffersComments;
use App\Models\Reports\Report;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityReportedOffersCommentsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $comment_id = null;
    protected $listeners = ['setCommentId', 'recountCounters'];
    public ?int $all_reports_count = null;
    public ?int $solved_reports_count = null;
    public ?int $pending_reports_count = null;


    public function render()
    {
        $this->all_reports_count = Report::where('reported_type', OffersComments::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', OffersComments::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', OffersComments::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        return view('livewire.pages.community.offers.comments.reports.index', [
            'comment_id' => $this->comment_id,
        ]);
    }

    /**
     * @param $active
     */
    public function changeActiveTab($active)
    {
        $this->page_type = $active;

        $this->emit('rerenderDataTable', ['status' => $active]);
    }

    /**
     * @param $id
     */
    public function setCommentId($id = null)
    {
        $this->comment_id = $id;
        if (!$id) {
            $this->emit('rerenderDataTable', ['status' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_reports_count = Report::where('reported_type', OffersComments::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', OffersComments::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', OffersComments::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();
    }
}
