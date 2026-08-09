<?php

namespace App\Http\Livewire\Interests;

use App\Helpers\Filter;
use App\Models\Interests\Interest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class InterestsCreateComponent extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    public ?string $parent_interest_id = null;
    public ?string $name_en = null;
    public ?string $name_ar = null;
    public ?string $description = null;
    public $image;
    public string $name_column;

    public $listeners = ['setParentInterestCreate'];

    /**
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
        'parent_interest_id' => ['nullable', "exists:interests,id"],
        'name_en' => ['required', "unique:interests,name_en"],
        'name_ar' => ['required', "unique:interests,name_ar"],
        'description' => ['nullable'],
        'image' => ['nullable'],
    ];

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get all interests
        $interests = Interest::get()
            ->map(function ($interest) {
                return [
                    'id' => $interest->id,
                    'name' => $interest[$this->name_column],
                ];
            });

        return view('livewire.pages.interests.create', [
            'interests' => $interests,
        ]);
    }

    public function store()
    {
        if (!Auth::guard('admin')->user()->can('interests.add')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }
        $this->validate();
        DB::beginTransaction();
        try {
            if ($this->image != null) {
                $url = $this->image->store('uploads/interests', 'local');
            } else {
                $url = null;
            }
            //set parent id
            $parent = ($this->parent_interest_id === '') ? null : $this->parent_interest_id;

            $data = [
                'parent_interest_id' => $parent,
                'name_en' => Filter::RemoveHtml($this->name_en),
                'name_ar' => Filter::RemoveHtml($this->name_ar),
                'description' => isset($this->description) ? Filter::RemoveHtml($this->description) : null,
                'image' => $url,
            ];
            Interest::create($data);
            $this->resetValidation();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);
            return null;
        }
        DB::commit();

        //flash toastr alert with success and redirect to the interests index
        return $this->flash('success', __('toastr.success'), [
            'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
        ], route('admin.interests.index'));
    }

    public function getAdminLanguage()
    {
        $name_column = Auth::guard('admin')->user()->language_code;
        if ($name_column === 'ar') {
            $this->name_column = 'name_ar';
        } else {
            $this->name_column = 'name_en';
        }
    }

    /**
     * set the value of parent interest id once it changes
     * @param $parent_interest_id
     */
    public function setParentInterestCreate($parent_interest_id)
    {
        $this->parent_interest_id = $parent_interest_id;

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
