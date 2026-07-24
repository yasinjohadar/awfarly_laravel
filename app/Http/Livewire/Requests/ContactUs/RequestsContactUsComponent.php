<?php

namespace App\Http\Livewire\Requests\ContactUs;

use App\Models\Requests\ContactForms;
use Livewire\Component;

class RequestsContactUsComponent extends Component
{
    public ?int $contact_id = null;
    public string $page_type = 'all';
    public int $all_count = 0;
    public int $read_count = 0;
    public int $unread_count = 0;
    protected $listeners = [
        'setContactId',
        'refreshData'
    ];


    public function render()
    {
        $this->all_count = ContactForms::count();
        $this->read_count = ContactForms::where('status', 'read')
            ->count();
        $this->unread_count = ContactForms::where('status', 'unread')
            ->count();

        return view('livewire.pages.requests.contact-us.index', [
            'contact_id' => $this->contact_id,
        ]);
    }

    /**
     * @param $id
     */
    public function setContactId($id)
    {
        $this->contact_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['status' => $this->page_type]);
        }
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
        $this->all_count = ContactForms::count();
        $this->read_count = ContactForms::where('status', 'read')
            ->count();
        $this->unread_count = ContactForms::where('status', 'unread')
            ->count();
    }
}
