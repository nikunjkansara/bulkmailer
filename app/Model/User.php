<?php

/**
 * User class.
 *
 * Model class for user
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

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use horsefly\Notifications\ResetPassword;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->hasOne('horsefly\Model\Customer');
    }

    public function admin()
    {
        return $this->hasOne('horsefly\Model\Admin');
    }

    public function systemJobs()
    {
        return $this->hasMany('horsefly\Model\SystemJob')->orderBy('created_at', 'desc');
    }

    /**
     * Get authenticate from file.
     *
     * @return string
     */
    public static function getAuthenticateFromFile()
    {
        $path = base_path('.authenticate');

        if (file_exists($path)) {
            $content = \File::get($path);
            $lines = explode("\n", $content);
            if (count($lines) > 1) {
                $demo = session()->get('demo');
                if (!isset($demo) || $demo == 'backend') {
                    return ['email' => $lines[0], 'password' => $lines[1]];
                } else {
                    return ['email' => $lines[2], 'password' => $lines[3]];
                }
            }
        }

        return ['email' => '', 'password' => ''];
    }

    /**
     * Send regitration activation email.
     *
     * @return string
     */
    public function sendActivationMail($name = null)
    {
        $layout = \horsefly\Model\Layout::where('alias', 'registration_confirmation_email')->first();
        $token = $this->getToken();

        $layout->content = str_replace('{ACTIVATION_URL}', join_url(config('app.url'), action('UserController@activate', ['token' => $token], false)), $layout->content);
        $layout->content = str_replace('{CUSTOMER_NAME}', $name, $layout->content);

        $name = is_null($name) ? trans('messages.to_email_name') : $name;
        \Mail::to(json_decode(json_encode(['email' => $this->email, 'name' => $name])))->send(new \horsefly\Mail\RegistrationConfirmationMailer($layout->content, $layout->subject));
    }

    /**
     * User activation.
     *
     * @return string
     */
    public function userActivation()
    {
        return $this->hasOne('horsefly\Model\userActivation');
    }

    /**
     * Create activation token for user.
     *
     * @return string
     */
    public function getToken()
    {
        $token = \horsefly\Model\UserActivation::getToken();

        $userActivation = $this->userActivation;

        if (!is_object($userActivation)) {
            $userActivation = new \horsefly\Model\UserActivation();
            $userActivation->user_id = $this->id;
        }

        $userActivation->token = $token;
        $userActivation->save();

        return $token;
    }

    /**
     * Set user is activated.
     *
     * @return bool
     */
    public function setActivated()
    {
        $this->activated = true;
        $this->save();
    }

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
            while (User::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Add api token
            $item->api_token = str_random(60);
        });
    }

    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Check if user has admin account.
     */
    public function isAdmin()
    {
        return is_object($this->admin);
    }

    /**
     * Get storage path.
     *
     * @return string
     */
    public function storagePath()
    {
        return base_path('public/source/'.$this->uid.'/');
    }

   /**
    * Send the password reset notification.
    *
    * @param  string  $token
    */
   public function sendPasswordResetNotification($token)
   {
       $this->notify(new ResetPassword($token, url('password/reset', $token)));
   }

    public function getStoragePath($path = '')
    {
        $base = storage_path('app/user/'.$this->uid);

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }
}
