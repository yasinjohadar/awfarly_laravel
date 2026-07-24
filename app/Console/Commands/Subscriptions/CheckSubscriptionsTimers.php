<?php

namespace App\Console\Commands\Subscriptions;

use App\Helpers\Settings;
use App\Models\Subscriptions\Packages\Advertisers\AdvertiserPackages;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSubscriptionsTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:subscriptions-timers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This option is to check subscriptions timers to set it as expired if it exceeded the time';

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
            //check advertisers packages
            $this->checkSubscriptions();

        } catch (Exception $exception) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * @return bool
     */
    public function checkSubscriptions(): bool
    {
        DB::beginTransaction();
        try {
            AdvertiserPackages::where('ends_at', '<', now())
                ->where('is_ended', false)
                ->where('is_active', true)
                ->get()
                ->each(function ($package) {
                    $active_packages = AdvertiserPackages::where('advertiser_id', $package->advertiser_id)
                        ->where('is_active', true)
                        ->where('is_ended', false)
                        ->where('is_current', true)
                        ->where('ends_at', '>', now())
                        ->exists();
                    if (!$active_packages) {
                        $package->advertiser()
                            ->update([
                                'is_elite' => false,
                                'allowed_posts_count' => Settings::Get('user.allowed.posts', 10)
                            ]);
                    } else {
                        $package->advertiser()
                            ->update([
                                'allowed_posts_count' => $package->package->maximum_posts ?? Settings::Get('user.allowed.posts', 10),
                            ]);
                    }
                    $package->update([
                        'is_ended' => true,
                        'is_current' => false,
                        'is_active' => false,
                    ]);
                });

        } catch (Exception $exception) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }
}
