<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Queue
        $schedule->command('queue:restart')->everyFiveMinutes();
        $schedule->command('queue:work --tries=1')->everyFourMinutes();

        // Force delete Soft deleted items exceeded 30 days with its media
        $schedule->command('check:deleted-items')->daily();

        // check users subscriptions to set packages as expired once it exceeds its end time
        $schedule->command('check:subscriptions-timers')->everyTenMinutes();

        //check online users status
        $schedule->command('check:online-users')->everyTenMinutes();

        //check viewed Posts that is added more than 5 mins ago
        $schedule->command('check:viewed-posts')->everyTenMinutes();

        //check viewed Offers that is added more than 5 mins ago
        $schedule->command('check:viewed-offers')->everyTenMinutes();

        //this console is to prune the saved requests in the database.
        $schedule->command('telescope:prune')->daily();

        //this console is to check advertisers remaining posts counter monthly and put default value for it.
        $schedule->command('check:advertisers-allowed-posts')->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
