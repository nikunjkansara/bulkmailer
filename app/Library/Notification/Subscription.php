<?php

/**
 * CronJobNotification class.
 *
 * Notification for cronjob issue
 *
 * LICENSE: This product includes software developed at
 * the HorseflyMailer. (http://horseflymailer.com/).
 *
 * @category   horsefly\
 *
 * @author     Nikunj K <nikunj@highvisiontech.com>
 * 
 * @copyright  HorseflyMailer
 * @license    HorseflyMailer
 *
 * @version    1.0
 *
 * @link       http://horseflymailer.com
 */

namespace horsefly\Library\Notification;

use horsefly\Model\Notification;
use horsefly\Cashier\Subscription as CashierSubscription;

class Subscription extends Notification
{
    /**
     * Check if CronJob is recently executed and log a notification if not.
     */
    public static function check()
    {
        self::cleanupSimilarNotifications();

        $count = CashierSubscription::where('status', CashierSubscription::STATUS_PENDING)->count();
        if ($count > 0) {
            self::info([
                'title' => trans('messages.admin.notification.subscription.title'),
                'message' => trans('messages.admin.notification.subscription.pending', ['count' => $count]),
            ]);
        }
    }
}
