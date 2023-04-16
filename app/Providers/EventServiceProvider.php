<?php

namespace horsefly\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'horsefly\Events\CampaignUpdated' => [
            'horsefly\Listeners\CampaignUpdatedListener',
        ],
        'horsefly\Events\MailListUpdated' => [
            'horsefly\Listeners\MailListUpdatedListener',
        ],
        'horsefly\Events\UserUpdated' => [
            'horsefly\Listeners\UserUpdatedListener',
        ],
        'horsefly\Events\CronJobExecuted' => [
            'horsefly\Listeners\CronJobExecutedListener',
        ],
        'horsefly\Events\AdminLoggedIn' => [
            'horsefly\Listeners\AdminLoggedInListener',
        ],
        'horsefly\Events\MailListSubscription' => [
            'horsefly\Listeners\MailListSubscriptionListener',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
