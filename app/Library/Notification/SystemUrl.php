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

class SystemUrl extends Notification
{
    /**
     * Check if CronJob is recently executed and log a notification if not.
     */
    public static function check()
    {
        self::cleanupSimilarNotifications();

        $current = url('/');
        $cached = config('app.url');
        if ($current != $cached) {
            $warning = [
                'title' => trans('messages.admin.notification.system_url_title'),
                'message' => trans('messages.admin.notification.system_url_not_match', ['cached' => $cached, 'current' => $current]),
            ];

            self::warning($warning);
        }
    }
}
