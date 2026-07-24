<?php

namespace App\Console\Commands\Users;

use App\Models\Users\Advertisers\AdvertiserUser;
use App\Models\Users\Customers\CustomerUser;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOnlineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:online-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console is to check online users that has not been pinged for more than 1 minute then set them to offline';

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
            $this->checkAdvertisers();
            $this->checkCustomers();
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
    public function checkAdvertisers(): bool
    {
        DB::beginTransaction();
        try {
            AdvertiserUser::where('is_online', true)
                ->where('last_online_at', '<', now()->subMinute())
                ->update([
                    'is_online' => false,
                ]);
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
    public function checkCustomers(): bool
    {
        DB::beginTransaction();
        try {
            CustomerUser::where('is_online', true)
                ->where('last_online_at', '<', now()->subMinute())
                ->update([
                    'is_online' => false,
                ]);

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }
}
