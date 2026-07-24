<?php

namespace App\Http\Livewire\Community\Offers\Comments;

use App\Models\Offers\Comments\OffersComments;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityOffersCommentsComponent extends Component
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
        $this->all_comments_count = OffersComments::withTrashed()
            ->count();

        /*$this->active_comments_count = PostComments::withoutTrashed()
            ->where('comments_count', '>', 0)
            ->count();*/

        $this->deleted_comments_count = OffersComments::onlyTrashed()
            ->count();

        if ($this->filter_id) {
            $activeNumberFilters = [
                '1' => [
                    'start' => $this->filter_id,
                    'end' => $this->filter_id,
                ]
            ];
        }

        return view('livewire.pages.community.offers.comments.index', [
            'activeNumberFilters' => $activeNumberFilters ?? [],
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
        $this->all_comments_count = OffersComments::withTrashed()
            ->count();

        /*$this->active_comments_count = PostComments::withoutTrashed()
            ->where('comments_count', '>', 0)
            ->count();*/

        $this->deleted_comments_count = OffersComments::onlyTrashed()
            ->count();
    }
}
