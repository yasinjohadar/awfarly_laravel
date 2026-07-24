<?php

namespace App\Http\Livewire\Requests\UsernameChange;

use App\Models\Requests\UsernameRequests;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class RequestsUsernameChangeComponent extends Component
{
    use LivewireAlert;

    public ?int $request_id = null;
    public string $page_type = 'all';
    public int $all_count = 0;
    public int $approved_count = 0;
    public int $declined_count = 0;
    public int $pending_count = 0;
    protected $listeners = [
        'setRequestId',
        'refreshData'
    ];


    public function render()
    {
        $this->all_count = UsernameRequests::count();
        $this->approved_count = UsernameRequests::where('status', 'approved')
            ->count();
        $this->declined_count = UsernameRequests::where('status', 'declined')
            ->count();
        $this->pending_count = UsernameRequests::where('status', 'pending')
            ->count();

        return view('livewire.pages.requests.change-username.index', ['request_id' => $this->request_id]);
    }

    public function setRequestId($id)
    {
        $this->request_id = $id;
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
     * refresh the counters once this listener is called
     */
    public function refreshData()
    {
        $this->all_count = UsernameRequests::count();
        $this->approved_count = UsernameRequests::where('status', 'approved')
            ->count();
        $this->declined_count = UsernameRequests::where('status', 'declined')
            ->count();
        $this->pending_count = UsernameRequests::where('status', 'pending')
            ->count();
    }
}
