<?php

namespace App\Console\Commands\Views;

use App\Models\Offers\Viewed\ViewedOffers;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ViewedOffersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:viewed-offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is to check viewed offers table and empty it every x minutes';

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
            ViewedOffers::all()
                ->each(function ($view) {
                    if (Carbon::parse($view->created_at)->addMinutes(5)->isPast()) {
                        $view->delete();
                    }
                });
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }
}
