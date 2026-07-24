<?php

namespace App\Http\Livewire\Community\Posts;

use App\Models\Posts\Post;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class CommunityPostsComponent extends Component
{
    use LivewireAlert;

    public string $page_type = 'all';
    private ?int $post_id = null;
    protected $listeners = ['setPostId', 'recountCounters'];
    public ?int $all_posts_count = null;
    public ?int $active_posts_count = null;
    public ?int $deleted_posts_count = null;
    public ?int $filter_id = null;

    public function render()
    {
        $this->all_posts_count = Post::withTrashed()
            ->whereNull('advertisement_id')
            ->count();

        $this->unreviewed_posts_count = Post::withTrashed()
            ->whereNull('advertisement_id')
            ->whereStatus('pending')
            ->count();

        $this->active_posts_count = Post::withoutTrashed()
            ->whereNull('advertisement_id')
            ->count();

        $this->deleted_posts_count = Post::onlyTrashed()
            ->whereNull('advertisement_id')
            ->count();

        if ($this->filter_id) {
            $activeNumberFilters = [
                '1' => [
                    'start' => $this->filter_id,
                    'end' => $this->filter_id,
                ]
            ];
        }

        return view('livewire.pages.community.posts.index', [
            'post_id' => $this->post_id,
            'activeNumberFilters' => $activeNumberFilters ?? []
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

    /**
     * @param $id
     */
    public function setPostId($id = null)
    {
        $this->post_id = $id;

        if (!$id) {
            $this->emit('rerenderDataTable', ['page_type' => $this->page_type]);
        }
    }

    public function recountCounters()
    {
        $this->all_posts_count = Post::withTrashed()
            ->whereNull('advertisement_id')
            ->count();

        $this->unreviewed_posts_count = Post::withTrashed()
            ->whereNull('advertisement_id')
            ->whereStatus('pending')
            ->count();

        $this->active_posts_count = Post::withoutTrashed()
            ->whereNull('advertisement_id')
            ->count();

        $this->deleted_posts_count = Post::onlyTrashed()
            ->whereNull('advertisement_id')
            ->count();
    }
}
