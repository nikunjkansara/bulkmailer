<?php

namespace horsefly\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use horsefly\Model\Setting;
use Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'horsefly\Model' => 'horsefly\Policies\ModelPolicy',
        \horsefly\Model\User::class => \horsefly\Policies\UserPolicy::class,
        \horsefly\Model\Contact::class => \horsefly\Policies\ContactPolicy::class,
        \horsefly\Model\MailList::class => \horsefly\Policies\MailListPolicy::class,
        \horsefly\Model\Subscriber::class => \horsefly\Policies\SubscriberPolicy::class,
        \horsefly\Model\Segment::class => \horsefly\Policies\SegmentPolicy::class,
        \horsefly\Model\Layout::class => \horsefly\Policies\LayoutPolicy::class,
        \horsefly\Model\Template::class => \horsefly\Policies\TemplatePolicy::class,
        \horsefly\Model\Campaign::class => \horsefly\Policies\CampaignPolicy::class,
        \horsefly\Model\SendingServer::class => \horsefly\Policies\SendingServerPolicy::class,
        \horsefly\Model\BounceHandler::class => \horsefly\Policies\BounceHandlerPolicy::class,
        \horsefly\Model\FeedbackLoopHandler::class => \horsefly\Policies\FeedbackLoopHandlerPolicy::class,
        \horsefly\Model\SendingDomain::class => \horsefly\Policies\SendingDomainPolicy::class,
        \horsefly\Model\Language::class => \horsefly\Policies\LanguagePolicy::class,
        \horsefly\Model\CustomerGroup::class => \horsefly\Policies\CustomerGroupPolicy::class,
        \horsefly\Model\Customer::class => \horsefly\Policies\CustomerPolicy::class,
        \horsefly\Model\AdminGroup::class => \horsefly\Policies\AdminGroupPolicy::class,
        \horsefly\Model\Admin::class => \horsefly\Policies\AdminPolicy::class,
        \horsefly\Model\Setting::class => \horsefly\Policies\SettingPolicy::class,
        \horsefly\Model\Plan::class => \horsefly\Policies\PlanPolicy::class,
        \horsefly\Model\Currency::class => \horsefly\Policies\CurrencyPolicy::class,
        \horsefly\Model\SystemJob::class => \horsefly\Policies\SystemJobPolicy::class,
        \horsefly\Cashier\Subscription::class => \horsefly\Policies\SubscriptionPolicy::class,
        \horsefly\Model\PaymentMethod::class => \horsefly\Policies\PaymentMethodPolicy::class,
        \horsefly\Model\EmailVerificationServer::class => \horsefly\Policies\EmailVerificationServerPolicy::class,
        \horsefly\Model\Blacklist::class => \horsefly\Policies\BlacklistPolicy::class,
        \horsefly\Model\SubAccount::class => \horsefly\Policies\SubAccountPolicy::class,
        \horsefly\Model\Sender::class => \horsefly\Policies\SenderPolicy::class,
        \horsefly\Model\Automation2::class => \horsefly\Policies\Automation2Policy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('send-from', function ($user, $fromEmail) {
            if (Setting::get('allow_send_from_unverified_domain') == 'yes') {
                return true;
            }

            $domain = substr(strrchr($fromEmail, '@'), 1);

            return $user->customer->activeSendingDomains()->where('name', $domain)->exists();
        });
    }
}
