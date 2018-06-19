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

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // Update teams on the 1st at 3am
        $schedule->command('betbuddies:import-team')
                  ->dailyAt('4:00');

        // Update games for today
        $schedule->command('betbuddies:import-games')
            ->everyMinute();

        // Keep the schedule up to date two days in advance
        $schedule->command('betbuddies:import-games '.strtotime('+2 days'))
            ->dailyAt(5);

        // Tweet the videos for today
        $schedule->command('betbuddies:tweet-highlights')
            ->everyFiveMinutes();

        // Tweet status messages for today
        $schedule->command('betbuddies:tweet-start-end')
            ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
