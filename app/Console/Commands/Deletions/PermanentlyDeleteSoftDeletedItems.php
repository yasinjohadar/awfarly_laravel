<?php

namespace App\Console\Commands\Deletions;

use App\Models\Chats\Messages\ChatMessages;
use App\Models\Offers\Comments\OffersComments;
use App\Models\Offers\Offer;
use App\Models\Posts\Comments\PostComments;
use App\Models\Posts\Post;
use App\Models\Proposals\Proposal;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Console\Command;
use Log;

class PermanentlyDeleteSoftDeletedItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:deleted-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is to check and permanently delete soft deleted items that has exceeded 30 days of soft delete';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle(): bool
    {
        DB::beginTransaction();
        try {
            //delete posts exceeded 30 days of soft deletion
            $this->deletePosts();

            //delete posts comments exceeded 30 days of soft deletion
            $this->deletePostsComments();

            //delete offers exceeded 30 days of soft deletion
            $this->deleteOffers();

            //delete offers comments exceeded 30 days of soft deletion
            $this->deleteOffersComments();

            //delete offers exceeded 30 days of soft deletion
            $this->deleteProposals();

            //delete advertisers packages exceeded 30 days of soft deletion
            $this->deleteAdvertisersPackages();

            //delete chat messages exceeded 30 days of soft deletion
            $this->deleteChatMessages();

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            $this->info($exception->getMessage());
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deletePosts(): bool
    {
        DB::beginTransaction();
        try {
            //check posts
            Post::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deletePostsComments(): bool
    {
        DB::beginTransaction();
        try {
            //check post comments
            PostComments::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deleteOffers(): bool
    {
        DB::beginTransaction();
        try {
            //check offers
            Offer::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deleteOffersComments(): bool
    {
        DB::beginTransaction();
        try {
            //check offers comments
            OffersComments::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deleteProposals(): bool
    {
        DB::beginTransaction();
        try {
            //check proposals
            Proposal::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deleteAdvertisersPackages(): bool
    {
        DB::beginTransaction();
        try {
            //check posts
            AdvertiserPackages::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function deleteChatMessages(): bool
    {
        DB::beginTransaction();
        try {
            //check posts
            ChatMessages::onlyTrashed()
                ->where('deleted_at', '<=', Carbon::now()->subMonth())
                ->get()
                ->each
                ->forceDelete();

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }
}
