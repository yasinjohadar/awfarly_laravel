<?php

namespace App\Http\Livewire\Customers\Reports;

use App\Models\Posts\Post;
use App\Models\Reports\Report;
use App\Models\Users\Customers\CustomerUser;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ReportedCustomersComponent extends Component
{
    use LivewireAlert;


    public string $page_type = 'all';
    private ?int $customer_id = null;
    protected $listeners = ['setCustomerId', 'recountCounters'];
    public ?int $all_reports_count = null;
    public ?int $solved_reports_count = null;
    public ?int $pending_reports_count = null;


    public function render()
    {
        $this->all_reports_count = Report::where('reported_type', CustomerUser::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', CustomerUser::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', CustomerUser::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        return view('livewire.pages.customers.reports.index', [
            'customer_id' => $this->customer_id,
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
    public function setCustomerId($id = null)
    {
        $this->customer_id = $id;
        if (!$id) {
            $this->emit('rerenderDataTable', ['status' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_reports_count = Report::where('reported_type', CustomerUser::class)
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->solved_reports_count = Report::where('reported_type', CustomerUser::class)
            ->where('status', 'solved')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();

        $this->pending_reports_count = Report::where('reported_type', CustomerUser::class)
            ->where('status', 'pending')
            ->groupBy('reported_id')
            ->distinct('reported_id')
            ->get()
            ->count();
    }
}
