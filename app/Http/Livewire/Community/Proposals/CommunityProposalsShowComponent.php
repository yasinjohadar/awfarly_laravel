<?php

namespace App\Http\Livewire\Community\Proposals;

use App\Helpers\Files;
use App\Helpers\Filter;
use App\Models\Proposals\Proposal;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class CommunityProposalsShowComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public int $proposal_id;
    public bool $showEditModal = false;
    public array $proposal = [];
    public $media;
    public array $order = [];
    public bool $showDeleteModal = false;
    public ?int $image_id = null;

    public function mount()
    {
        $this->setOrderData();
    }

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get proposal
        $proposal = Proposal::withTrashed()
            ->where('proposals.id', $this->proposal_id)
            ->first();

        return view('admin.pages.community.proposals.inquiry', [
            'proposal_data' => $proposal,
            'showEditModal' => $this->showEditModal,
        ]);
    }

    /**
     * show edit modal
     * @param $id
     */
    public function showEditModal($id)
    {
        //get user with data
        $this->proposal = Proposal::withTrashed()
            ->where('id', $id)
            ->select('id', 'content', 'answer')
            ->first()
            ->toArray();

        //show the modal
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        //close the modal
        $this->showEditModal = false;

        //empty user data
        $this->proposal = [];

        //reset validation messages
        $this->resetValidation();
    }

    public function update($id)
    {
        $this->validate([
            'proposal.content' => ['required'],
            'proposal.answer' => ['nullable'],
            'media' => ['required', 'max:10000',]
        ]);

        $data = $this->proposal;
        $data['content'] = Filter::RemoveHtml($this->proposal['content']);
        $data['answer'] = Filter::RemoveHtml($this->proposal['answer']);

        $proposal = Proposal::withTrashed()
            ->where('id', $id)
            ->first();

        DB::beginTransaction();
        try {
            $proposal->update($data);

            if ($this->media) {
                foreach ($this->media as $media) {
                    $mime_type = $media->getMimeType();
                    if (strstr($mime_type, "video/")) {
                        $file_width = null;
                        $file_height = null;
                    } else if (strstr($mime_type, 'image/')) {
                        $file_width = Image::load($media)->getWidth();
                        $file_height = Image::load($media)->getHeight();
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $file = $media->store('uploads/temp', 'local');
                    $proposal->addMediaFromDisk($file)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('proposals');
                    Files::deleteS3File($file);
                }
            }
            //close modal
            $this->closeEditModal();

            //reset validation messages
            $this->resetValidation();

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            $this->reset(['media']);

            $this->setOrderData();
            $this->dispatchBrowserEvent('resetLightGallery'/*, json_encode($media_data)*/);

            //dispatch event to refresh file input
            $this->dispatchBrowserEvent('clearFileInput', $this->order);

        } catch (Throwable $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
    }

    public function loadScripts()
    {
        $this->dispatchBrowserEvent('loadScripts');
    }

    /**
     * @param $id
     */
    public function deleteImage($id)
    {
        //get post
        $proposal = Proposal::withTrashed()
            ->where('id', $this->proposal_id)
            ->first();

        //get media
        $media = $proposal->getMedia('proposals')->where('id', $id)
            ->first();
        DB::beginTransaction();
        try {
            //delete media
            $media->delete();

            //send toastr alert with success
            $this->alert('success', __('toastr.delete'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->showDeleteModal = false;

            $this->dispatchBrowserEvent('resetLightGallery'/*, json_encode($media_data)*/);

        } catch (Throwable $e) {
            DB::rollBack();
            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();

    }


    /**
     * set new order for files
     */
    public function setOrder($data)
    {
        DB::beginTransaction();
        try {

            Media::setNewOrder($data);

            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

            $this->order = $data;

            $this->dispatchBrowserEvent('getData');
        } catch (Throwable $e) {
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                 'text' => $e->getMessage(),
            ]);

            return null;
        }
        DB::commit();
    }

    /**
     * show delete modal
     */
    public function showDeleteModal($id)
    {
        $this->image_id = $id;
        $this->showDeleteModal = true;
    }

    /**
     * show delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    public function setOrderData()
    {
        $this->order = [];
        //get post
        $post = Proposal::withTrashed()
            ->where('id', $this->proposal_id)
            ->first();

        $this->order = $post->getMedia('proposals')
            ->pluck('id')
            ->toArray();
    }
}
