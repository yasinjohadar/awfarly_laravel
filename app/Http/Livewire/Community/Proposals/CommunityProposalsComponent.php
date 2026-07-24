<?php

namespace App\Http\Livewire\Community\Proposals;

use App\Models\Proposals\Answers\ProposalAnswers;
use App\Models\Proposals\Proposal;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityProposalsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $proposal_id = null;
    protected $listeners = ['setProposalId', 'recountCounters'];
    public ?int $all_proposals_count = null;
    public ?int $answered_proposals_count = null;
    public ?int $unanswered_proposals_count = null;
    public ?int $filter_id = null;

    public function render()
    {
        $this->all_proposals_count = Proposal::withTrashed()
            ->count();

        $this->answered_proposals_count = Proposal::withTrashed()
            ->whereNotNull('answer')
            ->count();

        $this->unanswered_proposals_count = Proposal::withTrashed()
            ->whereNull('answer')
            ->count();

        if ($this->filter_id) {
            $activeNumberFilters = [
                '1' => [
                    'start' => $this->filter_id,
                    'end' => $this->filter_id,
                ]
            ];
        }

        return view('livewire.pages.community.proposals.index', [
            'proposal_id' => $this->proposal_id,
            'activeNumberFilters' => $activeNumberFilters ?? []
        ]);
    }

    /**
     * @param $active
     */
    public function changeActiveTab($active)
    {
        $this->page_type = $active;

        $this->emit('rerenderDataTable', ['page_type' => $active]);
    }

    /**
     * @param null $id
     */
    public function setProposalId($id = null)
    {
        $this->proposal_id = $id;


        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_proposals_count = Proposal::withTrashed()
            ->count();

        $this->answered_proposals_count = Proposal::withTrashed()
            ->whereNotNull('answer')
            ->count();

        $this->unanswered_proposals_count = Proposal::withTrashed()
            ->whereNull('answer')
            ->count();
    }
}
