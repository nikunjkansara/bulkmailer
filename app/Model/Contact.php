<?php

/**
 * Contact class.
 *
 * Model class for contacts
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

class Contact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'first_name', 'last_name', 'address_1', 'address_2', 'city', 'zip', 'url', 'company', 'phone', 'state', 'country_id',
        'tax_number', 'billing_address',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'email' => 'required|email',
        'first_name' => 'required',
        'last_name' => 'required',
        'address_1' => 'required',
        'city' => 'required',
        'zip' => 'required',
        'url' => 'regex:/^https{0,1}:\/\//',
        //'url' => 'url', # do not use the default 'url' validator of Laravel, otherwise, error: preg_match(): Compilation failed: invalid range in character class at offset 20
        'company' => 'required',
        'country_id' => 'required',
    );

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (Contact::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function country()
    {
        return $this->belongsTo('horsefly\Model\Country');
    }

    /**
     * Display contact name.
     *
     * @var string
     */
    public function name()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Display contact country name.
     *
     * @var string
     */
    public function countryName()
    {
        return is_object($this->country) ? $this->country->name : '';
    }
}
