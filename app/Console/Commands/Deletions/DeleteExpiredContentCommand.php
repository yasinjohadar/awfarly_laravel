<?php

namespace App\Console\Commands\Deletions;

use App\Helpers\Settings;
use App\Models\Offers\Offer;
use App\Models\Posts\Post;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteExpiredContentCommand extends Command
{
    protected $signature = 'check:expired-content';

    protected $description = 'Permanently delete posts and offers older than the configured retention days (0 disables)';

    public function handle(): int
    {
        $postsDeleted = $this->deleteAgedPosts();
        $offersDeleted = $this->deleteAgedOffers();

        $this->info("Expired content cleanup finished. Posts deleted: {$postsDeleted}. Offers deleted: {$offersDeleted}.");

        return self::SUCCESS;
    }

    protected function deleteAgedPosts(): int
    {
        $days = (int) Settings::Get('posts.auto_delete_after_days', 0);

        if ($days <= 0) {
            $this->line('Posts auto-delete is disabled (days <= 0).');
            return 0;
        }

        $cutoff = Carbon::now()->subDays($days);
        $deleted = 0;

        Post::withTrashed()
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($posts) use (&$deleted) {
                foreach ($posts as $post) {
                    try {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->postId', $post->id)
                            ->delete();

                        $post->clearMediaCollection('posts');
                        $post->forceDelete();
                        $deleted++;
                    } catch (Exception $e) {
                        Log::error('Failed to auto-delete post #' . $post->id . ': ' . $e->getMessage());
                        $this->error('Failed to delete post #' . $post->id . ': ' . $e->getMessage());
                    }
                }
            });

        return $deleted;
    }

    protected function deleteAgedOffers(): int
    {
        $days = (int) Settings::Get('offers.auto_delete_after_days', 0);

        if ($days <= 0) {
            $this->line('Offers auto-delete is disabled (days <= 0).');
            return 0;
        }

        $cutoff = Carbon::now()->subDays($days);
        $deleted = 0;

        Offer::withTrashed()
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($offers) use (&$deleted) {
                foreach ($offers as $offer) {
                    try {
                        DB::table('notifications')
                            ->whereJsonContains('data->customProperties->offerId', $offer->id)
                            ->delete();

                        $offer->clearMediaCollection('offers');
                        $offer->forceDelete();
                        $deleted++;
                    } catch (Exception $e) {
                        Log::error('Failed to auto-delete offer #' . $offer->id . ': ' . $e->getMessage());
                        $this->error('Failed to delete offer #' . $offer->id . ': ' . $e->getMessage());
                    }
                }
            });

        return $deleted;
    }
}
