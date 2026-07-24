<?php

namespace App\Http\Livewire\Advertisements\Comments;

use App\Models\Posts\Comments\PostComments;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AdvertisementsCommentsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    public ?int $filter_id = null;
    protected $listeners = ['recountCounters'];
    public ?int $all_comments_count = null;
    /*public ?int $active_comments_count = null;*/
    public ?int $deleted_comments_count = null;

    public function render()
    {
        $this->all_comments_count = PostComments::withTrashed()
            ->whereHas('post', function ($q) {
                $q->whereNotNull('advertisement_id');
            })
            ->count();

        /*$this->active_comments_count = PostComments::withoutTrashed()
            ->where('comments_count', '>', 0)
            ->count();*/

        $this->deleted_comments_count = PostComments::onlyTrashed()
            ->whereHas('post', function ($q) {
                $q->whereNotNull('advertisement_id');
            })
            ->count();

        if ($this->filter_id) {
            $activeTextFilters = [
                '2' => [
                    $this->filter_id,
                ]
            ];
        }

        return view('livewire.pages.advertisements.comments.index', [
            'activeTextFilters' => $activeTextFilters ?? [],
        ]);
    }

    /**
     * @param $active
     */
    public function changeActiveTab($active)
    {
        $this->page_type = $active;

        $this->emit('rerenderDataTable', ['page_type' => $active]);
    }

    public function recountCounters()
    {
        $this->all_comments_count = PostComments::withTrashed()
            ->whereHas('post', function ($q) {
                $q->whereNotNull('advertisement_id');
            })
            ->count();

        $this->deleted_comments_count = PostComments::onlyTrashed()
            ->whereHas('post', function ($q) {
                $q->whereNotNull('advertisement_id');
            })
            ->count();
    }
}
