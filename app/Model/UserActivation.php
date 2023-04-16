<?php

/**
 * UserActivation class.
 *
 * Model class for user activation
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

class UserActivation extends Model
{
    /**
     * Get user activation token.
     *
     * @return string
     */
    public static function getToken()
    {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }

    /**
     * User.
     *
     * @return string
     */
    public function user()
    {
        return $this->belongsTo('horsefly\Model\User');
    }
}
