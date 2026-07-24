<?php

namespace App\Http\Livewire\Advertisers\Reports;

use App\Models\Posts\Post;
use App\Models\Reports\Report;
use App\Models\Users\Advertisers\AdvertiserUser;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ReportedAdvertisersComponent extends Component
{

    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $advertiser_id = null;
    protected $listeners = ['setAdvertiserId', 'recountCounters'];
    public ?int $all_reports_count = null;
    public ?int $solved_reports_count = null;
    public ?int $pending_reports_count = null;


    public function render()
    {
        $this->all_reports_count = Report::where('reported_type', AdvertiserUser::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', AdvertiserUser::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', AdvertiserUser::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        return view('livewire.pages.advertisers.reports.index', [
            'advertiser_id' => $this->advertiser_id,
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
    public function setAdvertiserId($id = null)
    {
        $this->advertiser_id = $id;
        if (!$id) {
            $this->emit('rerenderDataTable', ['status' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_reports_count = Report::where('reported_type', AdvertiserUser::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', AdvertiserUser::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', AdvertiserUser::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();
    }
}
