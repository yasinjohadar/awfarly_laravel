<?php

namespace App\Console\Commands\Views;

use App\Models\Posts\Viewed\ViewedPost;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ViewedPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:viewed-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is to check viewed posts table and empty it every x minutes';

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
            ViewedPost::all()
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
