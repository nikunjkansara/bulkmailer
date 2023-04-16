<?php

/**
 * SubscriberField class.
 *
 * Model class for subscriber custom fields
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

class SubscriberField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'field_id', 'subscriber_id', 'value',
    ];

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function field()
    {
        return $this->belongsTo('horsefly\Model\Field');
    }
}
