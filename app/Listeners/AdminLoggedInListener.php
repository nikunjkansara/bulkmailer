<?php

namespace horsefly\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use horsefly\Library\Notification\CronJob;
use horsefly\Library\Notification\SystemUrl;
use horsefly\Library\Notification\Subscription;
use horsefly\Events\AdminLoggedIn;

class AdminLoggedInListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AdminLoggedIn  $event
     * @return void
     */
    public function handle(AdminLoggedIn $event)
    {
        // Check CronJob
        CronJob::check();
        
        // Check System URL
        SystemUrl::check();

        // Check for pending subscriptions
        Subscription::check();
    }
}
