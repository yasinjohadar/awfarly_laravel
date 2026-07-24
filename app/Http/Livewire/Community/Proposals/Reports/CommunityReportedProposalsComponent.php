<?php

namespace App\Http\Livewire\Community\Proposals\Reports;

use App\Models\Proposals\Proposal;
use App\Models\Reports\Report;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityReportedProposalsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $proposal_id = null;
    protected $listeners = ['setProposalId', 'recountCounters'];
    public ?int $all_reports_count = null;
    public ?int $solved_reports_count = null;
    public ?int $pending_reports_count = null;


    public function render()
    {
        $this->all_reports_count = Report::where('reported_type', Proposal::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', Proposal::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', Proposal::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        return view('livewire.pages.community.proposals.reports.index', [
            'proposal_id' => $this->proposal_id,
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
    public function setProposalId($id = null)
    {
        $this->proposal_id = $id;
        if (!$id) {
            $this->emit('rerenderDataTable', ['status' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_reports_count = Report::where('reported_type', Proposal::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', Proposal::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', Proposal::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();
    }
}
