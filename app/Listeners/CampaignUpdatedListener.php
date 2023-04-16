<?php

namespace horsefly\Listeners;

use horsefly\Events\CampaignUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use horsefly\Jobs\UpdateCampaignJob;
use horsefly\Jobs\UpdateMailListJob;
use horsefly\Model\SystemJob as SystemJobModel;

class CampaignUpdatedListener
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
     * @param  CampaignUpdated  $event
     * @return void
     */
    public function handle(CampaignUpdated $event)
    {
        if ($event->delayed) {
            $existed = SystemJobModel::getNewJobs()
                           ->where('name', UpdateCampaignJob::class)
                           ->where('data', $event->campaign->id)
                           ->exists();

            if (!$existed) {
                $existed = SystemJobModel::getNewJobs()
                           ->where('name', UpdateMailListJob::class)
                           ->whereIn('data', $event->campaign->mailLists->map(function($r) { return $r->id;  })->toArray())
                           ->exists();
            }

            if (!$existed) {
                dispatch(new UpdateCampaignJob($event->campaign));
            }
        } else {
            // @deprecated
            $event->campaign->updateCache();
        }
    }
}
