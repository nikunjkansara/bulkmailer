<?php

namespace horsefly\Listeners;

use horsefly\Events\UserUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use horsefly\Model\SystemJob as SystemJobModel;

class UserUpdatedListener
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
     * @param  UserUpdated  $event
     * @return void
     */
    public function handle(UserUpdated $event)
    {
        if ($event->delayed) {
            $existed = SystemJobModel::getNewJobs()
                           ->where('name', \horsefly\Jobs\UpdateUserJob::class)
                           ->where('data', $event->customer->id)
                           ->exists();
            if (!$existed) {
                dispatch(new \horsefly\Jobs\UpdateUserJob($event->customer));
            }
        } else {
            $event->customer->updateCache();
        }
    }
}
