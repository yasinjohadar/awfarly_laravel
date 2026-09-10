<?php

namespace App\Observers;

use App\Helpers\Notifications;
use App\Models\Posts\Post;

class PostObserver
{
    /**
     * Notify the post's advertiser whenever an admin approves or declines
     * (re-)review of their post — from any of the existing admin call sites
     * (list-page approve/reject, detail-page approve/reject, or the raw edit
     * form). `updated()` never fires on `create()`, so the initial
     * auto-approve-at-creation case is naturally excluded.
     *
     * @param Post $post
     * @return void
     */
    public function updated(Post $post): void
    {
        if ($post->wasChanged('status') && in_array($post->status, ['approved', 'unapproved'])) {
            Notifications::notifyOwnerPostStatusChanged($post);
        }
    }
}
