<?php

namespace horsefly\Listeners;

use horsefly\Library\Log as MailLog;
use horsefly\Model\Setting;
use horsefly\Events\CronJobExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CronJobExecutedListener
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
     * @param  CronJobExecuted  $event
     * @return void
     */
    public function handle(CronJobExecuted $event)
    {
        Setting::set('cronjob_last_execution', \Carbon\Carbon::now()->timestamp);
    }
}
