<?php

namespace App\Console\Commands\Users\Advertisers;

use App\Helpers\Advertisers\PackageQuotas;
use App\Models\Users\Advertisers\AdvertiserUser;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdvertisersAllowedPostsCounterCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:advertisers-allowed-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset free-tier advertisers remaining posts and offer ceilings to default settings';

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
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            AdvertiserUser::query()
                ->whereDoesntHave('packages', function ($q) {
                    $q->whereHas('package')
                        ->where('is_current', true)
                        ->where('is_active', true)
                        ->where('is_ended', false)
                        ->where('ends_at', '>', now());
                })
                ->get()
                ->each(function (AdvertiserUser $advertiser) {
                    PackageQuotas::applyFreeTier($advertiser);
                });
        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }
}
