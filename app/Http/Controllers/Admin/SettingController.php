<?php

namespace horsefly\Http\Controllers\Admin;

use Illuminate\Http\Request;
use horsefly\Http\Controllers\Controller;
use horsefly\Library\UpgradeManager;
use Illuminate\Support\Facades\Log;
use horsefly\Model\Setting;
use Illuminate\Support\Facades\Session;
use horsefly\Helpers\LicenseHelper;

class SettingController extends Controller
{
    /**
     * Display and update all settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_general') == 'yes') {
            return redirect()->action('Admin\SettingController@general');
        //} elseif ($request->user()->admin->getPermission('setting_sending') == 'yes') {
        //   return redirect()->action('Admin\SettingController@sending');
        } elseif ($request->user()->admin->getPermission('setting_system_urls') == 'yes') {
            echo "urls";exit;
            return redirect()->action('Admin\SettingController@urls');
        } elseif ($request->user()->admin->getPermission('setting_background_job') == 'yes') {
            echo "cronjobs";exit;
            return redirect()->action('Admin\SettingController@cronjob');
        }
    }

    /**
     * General settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function general(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_general') != 'yes') {
            return $this->notAuthorized();
        }

        // \horsefly\Model\Setting::updateAll();
        $settings = \horsefly\Model\Setting::getAll();
        if (null !== $request->old()) {
            foreach ($request->old() as $name => $value) {
                if (isset($settings[$name])) {
                    $settings[$name]['value'] = $value;
                }
            }
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            if ($this->isDemoMode()) {
                return $this->notAuthorized();
            }

            $rules = [
                'site_name' => 'required',
                'site_keyword' => 'required',
                'site_online' => 'required',
                'site_offline_message' => 'required',
                'site_description' => 'required',
                'frontend_scheme' => 'required',
                'backend_scheme' => 'required',
                'license' => 'license',
            ];
            $this->validate($request, $rules);

            // Save settings
            foreach ($request->all() as $name => $value) {
                if ($name != '_token' && isset($settings[$name])) {
                    // Upload and save image
                    if ($name == 'site_logo_small' || $name == 'site_logo_big' || $name == 'site_favicon') {
                        if ($request->hasFile($name) && $request->file($name)->isValid()) {
                            \horsefly\Model\Setting::uploadFile($request->file($name), $name);
                        }
                    } else {
                        if ($settings[$name]['cat'] == 'general' && $request->user()->admin->getPermission('setting_general') == 'yes') {
                            \horsefly\Model\Setting::set($name, $value);
                        }
                    }
                }
            }

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.setting.updated'));

            return redirect()->action('Admin\SettingController@general');
        }

        return view('admin.settings.general', [
            'settings' => $settings,
        ]);
    }

    /**
     * Sending settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function sending(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_sending') != 'yes') {
            return $this->notAuthorized();
        }

        // \horsefly\Model\Setting::updateAll();
        $settings = \horsefly\Model\Setting::getAll();
        if (null !== $request->old()) {
            foreach ($request->old() as $name => $value) {
                if (isset($settings[$name])) {
                    $settings[$name]['value'] = $value;
                }
            }
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            if ($this->isDemoMode()) {
                return $this->notAuthorized();
            }

            $rules = [
                'sending_campaigns_at_once' => 'required',
                'sending_change_server_time' => 'required',
                'sending_emails_per_minute' => 'required',
                'sending_pause' => 'required',
                'sending_at_once' => 'required',
                'sending_subscribers_at_once' => 'required',
            ];
            $this->validate($request, $rules);

            // Save settings
            foreach ($request->all() as $name => $value) {
                if ($name != '_token' && isset($settings[$name])) {
                    if ($settings[$name]['cat'] == 'sending' && $request->user()->admin->getPermission('setting_sending') == 'yes') {
                        \horsefly\Model\Setting::set($name, $value);
                    }
                }
            }

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.setting.updated'));

            return redirect()->action('Admin\SettingController@sending');
        }

        return view('admin.settings.sending', [
            'settings' => $settings,
        ]);
    }

    /**
     * Url settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function urls(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_system_urls') != 'yes') {
            return $this->notAuthorized();
        }

        $settings = \horsefly\Model\Setting::getAll();

        // Check URL
        $current = url('/');
        $cached = config('app.url');

        return view('admin.settings.urls', [
            'settings' => $settings,
            'matched' => ($cached == $current),
            'current' => $current,
            'cached' => $cached,
        ]);
    }

    /**
     * Cronjob list.
     *
     * @return \Illuminate\Http\Response
     */
    public function cronjob(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_background_job') != 'yes') {
            return $this->notAuthorized();
        }

        // Re-generate remote job url
        if ($request->re_generate_remote_job_url) {
            $remote_job_token = str_random(60);
            \horsefly\Model\Setting::set('remote_job_token', $remote_job_token);
            echo action('Controller@remoteJob', ['remote_job_token' => $remote_job_token]);

            return;
        }

        $respone = \horsefly\Library\Tool::cronjobUpdateController($request, $this);
        if ($respone == 'done' || $respone['valid'] == true) {
            $next = action('Admin\SettingController@cronjob').'#result_box';
            artisan_config_cache();

            return redirect()->away($next);
        }

        return view('admin.settings.cronjob', $respone);
    }

    /**
     * Mailer settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function mailer(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_general') != 'yes') {
            return $this->notAuthorized();
        }

        // SMTP
        $env = [
            'MAIL_DRIVER' => config('mail.driver'),
            'MAIL_HOST' => config('mail.host'),
            'MAIL_PORT' => config('mail.port'),
            'MAIL_USERNAME' => config('mail.username'),
            'MAIL_PASSWORD' => config('mail.password'),
            'MAIL_ENCRYPTION' => config('mail.encryption'),
            'MAIL_FROM_ADDRESS' => config('mail.from')['address'],
            'MAIL_FROM_NAME' => config('mail.from')['name'],
        ];

        if (null !== $request->old() && isset($request->old()['env'])) {
            foreach ($request->old()['env'] as $name => $value) {
                $env[$name] = $value;
            }
        }

        $env_rules = [
            'env.MAIL_DRIVER' => 'required',
            'env.MAIL_HOST' => 'required',
            'env.MAIL_PORT' => 'required',
            'env.MAIL_USERNAME' => 'required',
            'env.MAIL_PASSWORD' => 'required',
            'env.MAIL_FROM_ADDRESS' => 'required|email',
            'env.MAIL_FROM_NAME' => 'required',
        ];

        // validate and save posted data
        if ($request->isMethod('post')) {
            if ($this->isDemoMode()) {
                return $this->notAuthorized();
            }

            $env = $request->env;

            if ($env['MAIL_DRIVER'] == 'smtp') {
                $this->validate($request, $env_rules);
            }

            // Check SMTP connection
            $site_info = $request->all();
            if ($env['MAIL_DRIVER'] == 'smtp') {
                $rules = [];
                $messages = [];
                try {
                    $transport = new \Swift_SmtpTransport($env['MAIL_HOST'], $env['MAIL_PORT'], $env['MAIL_ENCRYPTION']);
                    $transport->setUsername($env['MAIL_USERNAME']);
                    $transport->setPassword($env['MAIL_PASSWORD']);
                    // @todo: it is not recommended to allow self-signed
                    $transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false)));
                    $mailer = new \Swift_Mailer($transport);
                    $mailer->getTransport()->start();
                } catch (\Swift_TransportException $e) {
                    $rules['smtp_valid'] = 'required';
                    $messages['required'] = $e->getMessage();
                } catch (\Exception $e) {
                    $rules['smtp_valid'] = 'required';
                    $messages['required'] = $e->getMessage();
                }
                $this->validate($request, $rules, $messages);
            }

            foreach ($env as $key => $value) {
                if (empty($value)) {
                    $value = 'null';
                }
                Log::info('Write to env '. $key . " Value:".$value);
                \horsefly\Model\Setting::setEnv($key, $value);
            }

            // Redirect to my lists page
            $next = action('Admin\SettingController@mailer');
            \Artisan::call('config:cache');
            $request->session()->flash('alert-success', trans('messages.setting.updated'));
            sleep(3);

            return redirect()->away($next);
        }

        return view('admin.settings.mailer', [
            'env_rules' => $env_rules,
            'env' => $env,
        ]);
    }

    /**
     * Update all urls.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUrls(Request $request)
    {
        // capture the current url, write to .env
        reset_app_url(true); // force update

        // @todo cannot use the artisan_config_cache() helper, cannot even call Artisan::call('config:cache') directly
        // \Artisan::call('config:cache');
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            unlink(base_path('bootstrap/cache/config.php'));
        }

        if ($request->user()->admin->getPermission('setting_system_urls') != 'yes') {
            return $this->notAuthorized();
        }

        \horsefly\Model\Setting::set('url_unsubscribe', action('CampaignController@unsubscribe', ['message_id' => 'MESSAGE_ID']));
        \horsefly\Model\Setting::set('url_open_track', action('CampaignController@open', ['message_id' => 'MESSAGE_ID']));
        \horsefly\Model\Setting::set('url_click_track', action('CampaignController@click', ['message_id' => 'MESSAGE_ID', 'url' => 'URL']));
        \horsefly\Model\Setting::set('url_delivery_handler', action('DeliveryController@notify', ['stype' => '']));
        \horsefly\Model\Setting::set('url_update_profile', action('PageController@profileUpdateForm', array(
            'list_uid' => 'LIST_UID',
            'uid' => 'SUBSCRIBER_UID',
            'code' => 'SECURE_CODE', ))
        );
        \horsefly\Model\Setting::set('url_web_view', action('CampaignController@webView', ['message_id' => 'MESSAGE_ID']));

        // Redirect to my lists page
        $request->session()->flash('alert-success', trans('messages.setting.updated'));

        return redirect()->action('Admin\SettingController@urls');
    }

    /**
     * View system logs.
     *
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request)
    {
        $path = base_path('artisan');
        $lines = 300;

        $error_logs = '';
        $file = file($path);
        for ($i = max(0, count($file) - $lines); $i < count($file); ++$i) {
            $error_logs .= $file[$i];
        }

        return view('admin.settings.logs', [
            'error_logs' => $error_logs,
        ]);
    }

    /**
     * View system logs.
     *
     * @return \Illuminate\Http\Response
     */
    public function download_log(Request $request)
    {
        $path = storage_path('logs/'.$request->file);

        return response()->download($path);
    }

    /**
     * License settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function license(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_general') != 'yes') {
            return $this->notAuthorized();
        }

        // \horsefly\Model\Setting::updateAll();
        $settings = \horsefly\Model\Setting::getAll();
        if (null !== $request->old()) {
            foreach ($request->old() as $name => $value) {
                if (isset($settings[$name])) {
                    $settings[$name]['value'] = $value;
                }
            }
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            if ($this->isDemoMode()) {
                return $this->notAuthorized();
            }

            try {
                // Update license type
                \horsefly\Model\Setting::updateLicense($request->license);

                // Save settings
                foreach ($request->all() as $name => $value) {
                    if ($name != '_token' && isset($settings[$name])) {
                        \horsefly\Model\Setting::set($name, $value);
                    }
                }

                // Redirect to my lists page
                $request->session()->flash('alert-success', trans('messages.license.updated'));

                return redirect()->action('Admin\SettingController@license');
            } catch (\Exception $ex) {
                $license_error = trans('messages.something_wrong_with_license_check', ['error' => $ex->getMessage()]);
            }
        }

        return view('admin.settings.license', [
            'settings' => $settings,
            'current_license' => \horsefly\Model\Setting::get('license'),
            'license_error' => isset($license_error) ? $license_error : '',
        ]);
    }

    /**
     * Upgrade manager page.
     *
     * @return \Illuminate\Http\Response
     */
    public function upgrade(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_upgrade_manager') != 'yes') {
            return $this->notAuthorized();
        }

        // secret key to send to the verification server
        session(['secret' => $request->input('secret')]);

        // Upgrade manager
        $manager = new UpgradeManager();

        // Redirect URL
        $pageUrl = action('Admin\SettingController@upgrade');

        // If upgrade done successfully, perform config:cache
        // The config:cache must be done in a subsequent process, not in the 'doUpgrade' one
        if ($request->session()->has('upgraded')) {
            $request->session()->forget('upgraded');
            $request->session()->put('reset', true);
            Session::save();
            $manager->refreshConfig();
            Setting::writeDefaultSettings();
            echo '<html>
                <head>
                    <meta http-equiv="refresh" content="3;'.$pageUrl.'" />
                <title>Page Moved</title>
                </head>
                <body>
                    Upgrade done, redirecting...
                </body>
                </html>';

            return;
        }

        if ($request->session()->has('reset')) {
            $pageUrl = action('Admin\SettingController@upgrade');
            $request->session()->forget('reset');
            Session::save();
            $request->session()->flash('alert-success', trans('messages.upgrade.alert.upgrade_success'));
        }

        return view('admin.settings.upgrade', [
            'any' => 'any',
            'manager' => $manager,
        ]);
    }

    /**
     * Upgrade manager page.
     *
     * @return \Illuminate\Http\Response
     */
    public function doUpgrade(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_upgrade_manager') != 'yes') {
            return $this->notAuthorized();
        }
        $manager = new UpgradeManager();
        $failed = $manager->test();
        if (empty($failed)) {
            $pageUrl = action('Admin\SettingController@upgrade');
            $manager->run();
            Log::info('System successfully upgraded to the new version');
            $request->session()->put('upgraded', true);
            // Redirect to an entirely new page
            // Then make a new request from browser to load new config
            // Simple using redirect() will retain the current setting
            echo '<html>
                <head>
                    <meta http-equiv="refresh" content="3;'.$pageUrl.'" />
                <title>Page Moved</title>
                </head>
                <body>
                    Upgrade is in progress, please wait...
                </body>
                </html>';

            return;
        } else {
            Log::warning('Cannot upgrade, certain files are not writable');

            return view('admin.settings.upgrade', [
                'any' => 'any',
                'manager' => $manager,
                'failed' => $failed,
            ]);
        }
    }

    /**
     * Cancel upgrade and delete the uploaded file.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelUpgrade(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_upgrade_manager') != 'yes') {
            return $this->notAuthorized();
        }

        try {
            $manager = new UpgradeManager();
            $manager->cleanup();
            $request->session()->flash('alert-info', trans('messages.upgrade.alert.cancel_success'));
        } catch (\Exception $e) {
            Log::info('Something went wrong while cancelling upgrade. '.$e->getMessage());
            $request->session()->flash('alert-error', $e->getMessage());
        }

        return redirect()->action('Admin\SettingController@upgrade');
    }

    /**
     * Upload the application patch.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadApplicationPatch(Request $request)
    {
        if (empty(Setting::get('license'))) {
            $request->session()->flash('alert-error', 'Please <a style="text-decoration:underline;font-style: normal;" href="'.url('admin/settings/license').'">register</a> your installation with a valid purchase code first before upgrading.');

            return redirect()->action('Admin\SettingController@upgrade');
        }

        try {
            list($supported, $supportedUntil) = LicenseHelper::isSupported();
            if (!$supported) {
                $request->session()->flash('alert-error', sprintf('Your Horsefly Mailer SUPPORT was already expired on <strong>%s</strong>. Please <a style="text-decoration:underline;font-style: normal;" target="_blank" href="%s">renew</a> it first before proceeding with our LIVE upgrade service.', $supportedUntil->toFormattedDateString(),  'https://codecanyon.net/item/acelle-email-marketing-web-application/17796082'));

                return redirect()->action('Admin\SettingController@upgrade');
            }
        } catch (\Exception $ex) {
            $request->session()->flash('alert-error', $ex->getMessage());

            return redirect()->action('Admin\SettingController@upgrade');
        }

        if ($request->user()->admin->getPermission('setting_upgrade_manager') != 'yes') {
            return $this->notAuthorized();
        }

        try {
            $manager = new UpgradeManager();
            // if file size exceeds "upload_max_filesize" ini directive
            // moving will end up fail with an exception
            // also, file('file')->path() will return the application root directory rather than the correct file path
            $request->file('file')->move(storage_path('tmp'), $request->file('file')->getClientOriginalName());
            $path = storage_path('tmp/'.$request->file('file')->getClientOriginalName());
            $manager->load($path);
            $request->session()->flash('alert-success', trans('messages.upgrade.alert.upload_success'));
        } catch (\Exception $e) {
            Log::info('Upgrade failed. '.$e->getMessage());
            $request->session()->flash('alert-error', $e->getMessage());
        }

        return redirect()->action('Admin\SettingController@upgrade');
    }

    /**
     * Advanced settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function advanced(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_general') != 'yes') {
            return $this->notAuthorized();
        }

        // \horsefly\Model\Setting::updateAll();
        $settings = \horsefly\Model\Setting::getAll();
        if (null !== $request->old()) {
            foreach ($request->old() as $name => $value) {
                if (isset($settings[$name])) {
                    $settings[$name]['value'] = $value;
                }
            }
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            if ($this->isDemoMode()) {
                return $this->notAuthorized();
            }

            $rules = [];
            $this->validate($request, $rules);

            // Save settings
            foreach ($request->all() as $name => $value) {
                if ($name != '_token' && isset($settings[$name])) {
                    // Upload and save image
                    if ($name == 'site_logo_small' || $name == 'site_logo_big') {
                        if ($request->hasFile($name) && $request->file($name)->isValid()) {
                            \horsefly\Model\Setting::uploadSiteLogo($request->file($name), $name);
                        }
                    } else {
                        if ($settings[$name]['cat'] == 'advanced' && $request->user()->admin->getPermission('setting_general') == 'yes') {
                            \horsefly\Model\Setting::set($name, $value);
                        }
                    }
                }
            }

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.setting.updated'));

            return redirect()->action('Admin\SettingController@advanced');
        }

        return view('admin.settings.advanced', [
            'settings' => $settings,
        ]);
    }

    /**
     * Advanced settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function advancedUpdate(Request $request, $name)
    {
        if ($request->user()->admin->getPermission('setting_general') != 'yes') {
            return $this->notAuthorized();
        }

        if ($this->isDemoMode()) {
            return $this->notAuthorized();
        }

        // update setting value
        \horsefly\Model\Setting::set($name, $request->value);

        echo trans('messages.setting.update.success', ['name' => $name]);
    }

    /**
     * Payment gateway settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request)
    {
        if ($request->user()->admin->getPermission('setting_general') != 'yes') {
            return $this->notAuthorized();
        }

        // \horsefly\Model\Setting::updateAll();
        $settings = \horsefly\Model\Setting::getAll();

        if ($this->isDemoMode()) {
            return $this->notAuthorized();
        }

        $rules = [
            'end_period_last_days' => 'required',
            'renew_free_plan' => 'required',
            'recurring_charge_before_days' => 'required',
        ];
        $this->validate($request, $rules);

        // Save settings
        \horsefly\Model\Setting::set('system.end_period_last_days', $request->end_period_last_days);
        \horsefly\Model\Setting::set('system.renew_free_plan', $request->renew_free_plan);
        \horsefly\Model\Setting::set('system.recurring_charge_before_days', $request->recurring_charge_before_days);

        // Redirect to my lists page
        $request->session()->flash('alert-success', trans('messages.setting.updated'));

        return redirect()->action('Admin\PaymentController@index');
    }
}
