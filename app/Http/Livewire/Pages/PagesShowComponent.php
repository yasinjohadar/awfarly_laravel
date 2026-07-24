<?php

namespace App\Http\Livewire\Pages;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Models\Categories\Category;
use App\Models\Pages\Page;
use App\Models\Posts\Post;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class PagesShowComponent extends Component
{
    use LivewireAlert;

    use WithFileUploads;

    public int $page_id;
    public string $contents_en;
    public string $contents_ar;
    public Page $page;

    /**
     * @return Application|Factory|View
     */
    public function render()
    {
        //get page
        $page = Page::where('id', $this->page_id)
            ->first();
        $this->page = $page;
        $this->contents_en = $page->contents_en;
        $this->contents_ar = $page->contents_ar;


        return view('livewire.pages.pages.edit');
    }

    public function update()
    {
        if (!Auth::guard('admin')->user()->can('pages.edit')) {
            //send toastr alert with error
            $this->alert('error', __('permissions.insufficient_permissions'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);
            return null;
        }

        $page_data = Page::findOrFail($this->page_id);

        //validate data
        $this->validate([
            'contents_en' => ['required'],
            'contents_ar' => ['required'],
        ]);
        $data['contents_en'] = $this->contents_en;
        $data['contents_ar'] = $this->contents_ar;

        DB::beginTransaction();
        try {
            //add log
            AdminLogs::log('edit', 'customers', [
                'old' => $this->page,
                'new' => $data,
            ], "Edit: page #$this->page_id");

            //update page_data
            $page_data->update($data);

            //reset validation messages
            $this->resetValidation();
            //send toastr alert with success
            $this->alert('success', __('toastr.success'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
            ]);

        } catch (Exception $e) {
            //rollback
            DB::rollBack();

            //send toastr alert with error
            $this->alert('error', __('toastr.error'), [
                'position' => ((App::currentLocale() === 'ar') ? 'top-start' : 'top-end'),
                'text' => $e->getMessage(),
            ]);

            return null;
        }
        //commit
        DB::commit();
    }

}
