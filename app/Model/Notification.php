<?php

/**
 * Notification class.
 *
 * Model class for notifications
 *
 * LICENSE: This product includes software developed at
 * the HorseflyMailer. (http://horseflymailer.com/).
 *
 * @category   MVC Model
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

namespace horsefly\Model;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    protected $fillable = [
        'type',
        'title',
        'message',
        'level',
        'uid',
    ];

    /**
     * Create an INFO notification.
     *
     * @return notification record
     */
    public static function info($attributes, $cleanup = true)
    {
        $default = [
            'level' => static::LEVEL_INFO,
        ];

        $attributes = array_merge($default, $attributes);

        return static::add($attributes, $cleanup);
    }

    /**
     * Create an WARNING notification.
     *
     * @return notification record
     */
    public static function warning($attributes, $cleanup = true)
    {
        $default = [
            'level' => static::LEVEL_WARNING,
        ];

        $attributes = array_merge($default, $attributes);

        return static::add($attributes, $cleanup);
    }

    /**
     * Create an ERROR notification.
     *
     * @return notification record
     */
    public static function error($attributes, $cleanup = true)
    {
        $default = [
            'level' => static::LEVEL_ERROR,
        ];

        $attributes = array_merge($default, $attributes);

        return static::add($attributes, $cleanup);
    }

    /**
     * Actually insert the notification record to the notifications table.
     *
     * @return notification record
     */
    public static function add($attributes, $cleanup = true)
    {
        $default = [
            'type' => get_called_class(),
            'uid' => uniqid(),
        ];
        $attributes = array_merge($default, $attributes);
        if ($cleanup) {
            self::cleanupSimilarNotifications();
        }

        return self::create($attributes);
    }

    /**
     * Get top notifications.
     *
     * @return notification records
     */
    public static function top($limit = 3)
    {
        return static::where('visibility', true)->limit($limit)->get();
    }

    /**
     * Clean up notifications of the same type as the called class.
     */
    public static function cleanupSimilarNotifications()
    {
        static::where('type', get_called_class())->delete();
    }

    /**
     * Clean up notifications of the same type / title as the called class.
     */
    public static function cleanupDuplicateNotifications($title)
    {
        static::where('title', $title)->delete();
    }

    /**
     * Clean up all notifications.
     */
    public static function cleanup()
    {
        static::query()->delete();
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Hide item.
     *
     * @return object
     */
    public function hide()
    {
        $this->visibility = false;

        return $this->save();
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $query = self::select('notifications.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('message', 'like', '%'.$request->keyword.'%');
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['level'])) {
                $query = $query->where('notifications.level', '=', $filters['level']);
            }
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }
}
