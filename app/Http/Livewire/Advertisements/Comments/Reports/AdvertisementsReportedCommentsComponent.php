<?php

namespace App\Http\Livewire\Advertisements\Comments\Reports;

use App\Models\Posts\Comments\PostComments;
use App\Models\Reports\Report;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AdvertisementsReportedCommentsComponent extends Component
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
        $this->all_reports_count = Report::where('reported_type', PostComments::class)
            ->whereHasMorph('reported', [PostComments::class], function ($q) {
                $q->whereHas('post', function ($query) {
                    $query->whereNotNull('advertisement_id');
                });
            })
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', PostComments::class)
            ->whereHasMorph('reported', [PostComments::class], function ($q) {
                $q->whereHas('post', function ($query) {
                    $query->whereNotNull('advertisement_id');
                });
            })
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', PostComments::class)
            ->whereHasMorph('reported', [PostComments::class], function ($q) {
                $q->whereHas('post', function ($query) {
                    $query->whereNotNull('advertisement_id');
                });
            })
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        return view('livewire.pages.advertisements.comments.reports.index', [
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
        $this->all_reports_count = Report::where('reported_type', PostComments::class)
            ->whereHasMorph('reported', [PostComments::class], function ($q) {
                $q->whereHas('post', function ($query) {
                    $query->whereNotNull('advertisement_id');
                });
            })
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', PostComments::class)
            ->whereHasMorph('reported', [PostComments::class], function ($q) {
                $q->whereHas('post', function ($query) {
                    $query->whereNotNull('advertisement_id');
                });
            })
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', PostComments::class)
            ->whereHasMorph('reported', [PostComments::class], function ($q) {
                $q->whereHas('post', function ($query) {
                    $query->whereNotNull('advertisement_id');
                });
            })
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();
    }
}
