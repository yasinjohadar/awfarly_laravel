<?php

namespace App\Http\Livewire\Modals;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Throwable;
use App\Helpers\Filter;
use Livewire\Component;
use App\Models\Modals\Modal;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class ModalsCreateComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public string $recipients_type = 'all_users';
    public ?string $parent_modal_id = null;
    public ?string $link = null;
    public ?string $title_en = null;
    public ?string $title_ar = null;
    public ?string $body_en = null;
    public ?string $body_ar = null;
    public ?string $start_at = null;
    public ?string $end_at = null;
    public string $name_column;

    public $listeners = ['setParentModalCreate'];

    /**
     * ModalCreateComponent constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->getAdminLanguage();
        parent::__construct($id);
    }

    /**
     * @var array
     */
    protected array $rules = [
        'recipients_type' => ['required'],
        'link' => ['sometimes', 'nullable', 'url'],
        'title_en' => ['required'],
        'title_ar' => ['required'],
        'body_en' => ['required', 'string'],
        'body_ar' => ['required', 'string'],
        'start_at' => ['required'],
        'end_at' => ['required'],
    ];

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get all modals
        $modals = Modal::get()
            ->map(function ($modal) {
                return [
                    'id' => $modal->id,
                    'name' => $modal[$this->name_column],
                ];
            });

        return view('livewire.pages.modals.create', [
            'modals' => $modals,
        ]);
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('modal.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate();
        DB::beginTransaction();
        try {

            $data = [
                'recipients_type' => Filter::RemoveHtml($this->recipients_type),
                'link' => $this->link,
                'title_en' => Filter::RemoveHtml($this->title_en),
                'title_ar' => Filter::RemoveHtml($this->title_ar),
                'body_en' => isset($this->body_en) ? Filter::RemoveHtml($this->body_en) : null,
                'body_ar' => isset($this->body_ar) ? Filter::RemoveHtml($this->body_ar) : null,
                'start_at' => isset($this->start_at) ? Filter::RemoveHtml($this->start_at) : null,
                'end_at' => isset($this->end_at) ? Filter::RemoveHtml($this->end_at) : null,
            ];
            Modal::create($data);
            $this->resetValidation();
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset([
                'recipients_type',
                'link',
                'title_en',
                'title_ar',
                'body_en',
                'body_ar',
                'start_at',
                'end_at',
            ]);

            //dispatch event to refresh select2
            $this->dispatchBrowserEvent('refreshSelect2Create');

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        DB::commit();
    }

    public function getAdminLanguage()
    {
        $name_column = Auth::guard('admin')->user()->language_code;
        if ($name_column === 'ar') {
            $this->name_column = 'title_ar';
        } else {
            $this->name_column = 'title_en';
        }
    }

    /**
     * set the value of parent modal id once it changes
     * @param $parent_modal_id
     */
    public function setParentModalCreate($parent_modal_id)
    {
        $this->parent_modal_id = $parent_modal_id;

        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }

    /**
     * dispatch select2 modal while updating
     */
    public function updating()
    {
        //dispatch event to refresh select2
        $this->dispatchBrowserEvent('refreshSelect2Create');
    }
}
