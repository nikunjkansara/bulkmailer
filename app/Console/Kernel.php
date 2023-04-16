<?php

namespace horsefly\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use horsefly\Model\Automation2;
use horsefly\Cashier\Cashier;
use horsefly\Cashier\Subscription;
use horsefly\Model\Setting;
use Laravel\Tinker\Console\TinkerCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        /* no longer needed as of Laravel 5.5
        Commands\TestCampaign::class,
        Commands\UpgradeTranslation::class,
        Commands\RunHandler::class,
        Commands\ImportList::class,
        Commands\VerifySender::class,
        Commands\SystemCleanup::class,
        Commands\GeoIpCheck::class,
        TinkerCommand::class,
        */
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        echo "\nschedule:run";
        // Log last execution time
        // Move the event into a schedule::call to prevent it from triggering every time "php artisan" command is executed
        $schedule->call(function () {
            echo "\ncronjob_event:log";
            event(new \horsefly\Events\CronJobExecuted());
        })->name('cronjob_event:log')->everyMinute();

        // Automation2
        $schedule->call(function () {
            echo "\nautomation:run";
            Automation2::run();
        })->name('automation:run')->everyFiveMinutes();

        // Bounce/feedback handler
        $schedule->command('handler:run')->everyThirtyMinutes();

        // Queued import/export/campaign
        $schedule->command('queue:work --once --tries=1')->everyMinute();

        // Sender verifying
        $schedule->command('sender:verify')->everyFiveMinutes();

        // System clean up
        $schedule->command('system:cleanup')->daily();

        // GeoIp database check
        $schedule->command('geoip:check')->everyMinute()->withoutOverlapping();

        // Subscription
        $schedule->call(function () {
            // $gateway = \App::make('horsefly\Cashier\PaymentGateway');
            $gateway = Cashier::getPaymentGateway();
            Subscription::checkAll($gateway);
        })->name('subscription:run')->everyFiveMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
