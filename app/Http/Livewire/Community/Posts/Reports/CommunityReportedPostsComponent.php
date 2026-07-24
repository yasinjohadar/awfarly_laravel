<?php

namespace App\Http\Livewire\Community\Posts\Reports;

use App\Models\Posts\Post;
use App\Models\Reports\Report;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityReportedPostsComponent extends Component
{
    use LivewireAlert;


    public string $page_type = 'all';
    private ?int $post_id = null;
    protected $listeners = ['setPostId', 'recountCounters'];
    public ?int $all_reports_count = null;
    public ?int $solved_reports_count = null;
    public ?int $pending_reports_count = null;


    public function render()
    {
        $this->all_reports_count = Report::where('reported_type', Post::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', Post::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', Post::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        return view('livewire.pages.community.posts.reports.index', [
            'post_id' => $this->post_id,
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
    public function setPostId($id = null)
    {
        $this->post_id = $id;
        if (!$id) {
            $this->emit('rerenderDataTable', ['status' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_reports_count = Report::where('reported_type', Post::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', Post::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', Post::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();
    }
}
