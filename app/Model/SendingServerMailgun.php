<?php

/**
 * SendingServerMailgun class.
 *
 * Abstract class for Mailgun sending servers
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

use horsefly\Library\Log as MailLog;
use horsefly\Library\StringHelper;
use Mailgun\Mailgun;

class SendingServerMailgun extends SendingServer
{
    const WEBHOOK = 'mailgun';

    protected $table = 'sending_servers';
    public static $client = null;
    public static $isWebhookSetup = false;

    // Inherit class to implementation of this method
    public function send($message, $params = array())
    {
        // for overwriting
    }

    /**
     * Get authenticated to Mailgun and return the session object.
     *
     * @return mixed
     */
    public function client()
    {
        if (!self::$client) {
            self::$client = new Mailgun($this->api_key);
        }

        return self::$client;
    }

    /**
     * Setup webhooks for processing bounce and feedback loop.
     *
     * @return mixed
     */
    public function setupWebhooks()
    {
        if (self::$isWebhookSetup) {
            return true;
        }

        MailLog::info('Setting up webhooks for bounce/complaints');

        $domain = $this->domain;
        $subscribeUrl = StringHelper::joinUrl(Setting::get('url_delivery_handler'), self::WEBHOOK);

        MailLog::info('Webhook set to: '.$subscribeUrl);

        try {
            $result = $this->client()->delete("domains/$domain/webhooks/bounce");
        } catch (\Exception $e) {
            // just ignore
        }

        try {
            $result = $this->client()->delete("domains/$domain/webhooks/spam");
        } catch (\Exception $e) {
            // just ignore
        }

        $result = $this->client()->post("domains/$domain/webhooks", array(
            'id' => 'bounce',
            'url' => $subscribeUrl,
        ));

        MailLog::info('Bounce webhook created');

        $result = $this->client()->post("domains/$domain/webhooks", array(
            'id' => 'spam',
            'url' => $subscribeUrl,
        ));

        MailLog::info('Complaint webhook created');

        self::$isWebhookSetup = true;
    }

    /**
     * Get verified identities (domains and email addresses).
     *
     * @return bool
     */
    public function getVerifiedIdentities()
    {
        $response = $this->client()->get('domains');
        $json = json_decode(json_encode($response), true);

        return array_map(function ($domain) { return $domain['name']; }, $json['http_response_body']['items']);
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        $response = $this->client()->get('domains');

        return true;
    }
}
