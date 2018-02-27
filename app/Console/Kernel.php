<?php

namespace App\Console;

use App\Helpers\ScheduledTasks;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemperatureController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\AlertController;

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // $schedule->command('inspire')
        //          ->hourly();


        //schedule for alarm
        $schedule->call(function(){
            ScheduledTasks::detectAlarms();
        })->dailyAt('09:00')->name('detect_alarms')->withoutOverlapping();



        //schedule for auto reporting mechanism
        $schedule->call(function(){
            ReportController::performPeriodicReport();
        })->dailyAt('08:00')->name('process_reports')->withoutOverlapping();

        //schedule for temperature data
        $schedule->call(function(){
            TemperatureController::traverseAirports();
        })->dailyAt('01:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
