<?php

/**
 * SendingServerSendGrid class.
 *
 * Abstract class for SendGrid sending servers
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
use SendGrid\Mail;
use SendGrid\Email;
use SendGrid\Content;

class SendingServerSendGrid extends SendingServer
{
    const WEBHOOK = 'sendgrid';

    protected $table = 'sending_servers';
    public static $client = null;
    public static $isWebhookSetup = false;

    /**
     * Get authenticated to Mailgun and return the session object.
     *
     * @return mixed
     */
    public function client()
    {
        if (!self::$client) {
            if (is_null($this->subAccount)) {
                MailLog::info('Using master account');
                self::$client = new \SendGrid($this->api_key);
            } else {
                MailLog::info("Using subaccount {$this->subAccount->getSubAccountUsername()}");
                self::$client = new \SendGrid($this->subAccount->api_key);
            }
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

        MailLog::info('Setting up SendGrid webhooks');
        $subscribeUrl = StringHelper::joinUrl(Setting::get('url_delivery_handler'), self::WEBHOOK);
        $request_body = json_decode('{
            "bounce": true,
            "click": false,
            "deferred": false,
            "delivered": false,
            "dropped": true,
            "enabled": true,
            "group_resubscribe": false,
            "group_unsubscribe": false,
            "open": false,
            "processed": false,
            "spam_report": true,
            "unsubscribe": false,
            "url": "'.$subscribeUrl.'"
            }'
        );
        $response = $this->client()->client->user()->webhooks()->event()->settings()->patch($request_body);

        if ($response->statusCode() == '200') {
            MailLog::info('Webhooks successfully set!');
        } else {
            throw new \Exception(sprintf('Cannot setup SendGrid webhook. Status code: %s. Body: %s', $response->statusCode(), $response->body()));
        }

        self::$isWebhookSetup = true;
    }

    /**
     * Get Message Id
     * Extract the message id from SendGrid response.
     *
     * @return string
     */
    public function getMessageId($headers)
    {
        preg_match('/(?<=X-Message-Id: ).*/', $headers, $matches);
        if (isset($matches[0])) {
            return $matches[0];
        } else {
            return;
        }
    }

    /**
     * Prepare the email object for sending.
     *
     * @return mixed
     */
    public function prepareEmail($message)
    {
        $fromEmail = array_keys($message->getFrom())[0];
        $fromName = (is_null($message->getFrom())) ? null : array_values($message->getFrom())[0];
        $toEmail = array_keys($message->getTo())[0];
        $toName = (is_null($message->getTo())) ? null : array_values($message->getTo())[0];
        $replyToEmail = (is_null($message->getReplyTo())) ? $fromEmail : array_keys($message->getReplyTo())[0];

        // Following RFC 1341, section 7.2, if either text/html or text/plain are to be sent in your email: text/plain needs to be first
        // So, use array_shift instead of array_pop
        // Also, sort the parts so that text/plain comes before text/html

        $parts = $message->getChildren();
        usort($parts, function ($a, $b) { return ($a->getContentType() == 'text/plain') ? -1 : 1; });

        $parts = array_map(function ($part) {
            return new Content($part->getContentType(), $part->getBody());
        }, $parts);

        $mail = new Mail(
            new Email($fromName, $fromEmail),
            $message->getSubject(),
            new Email($toName, $toEmail),
            array_shift($parts)
        );

        // set Reply-To header
        $mail->setReplyTo(['email' => $replyToEmail]);

        foreach ($parts as $part) {
            $mail->addContent($part);
        }

        $preserved = [
            'Content-Transfer-Encoding',
            'Content-Type',
            'MIME-Version',
            'Date',
            'Message-ID',
            'From',
            'Subject',
            'To',
            'Reply-To',
            'Subject',
            'From',
        ];

        foreach ($message->getHeaders()->getAll() as $header) {
            if (!in_array($header->getFieldName(), $preserved)) {
                $mail->addHeader($header->getFieldName(), $header->getFieldBody());
            }
        }

        // to track bounce/feedback notification
        $mail->addCustomArg('runtime_message_id', $message->getHeaders()->get('X-Acelle-Message-Id')->getFieldBody());

        return $mail;
    }

    /**
     * Get verified identities (domains and email addresses).
     *
     * @return bool
     */
    public function getVerifiedIdentities()
    {
        $response = $this->client()->client->whitelabel()->domains()->get();
        $json = json_decode($response->body(), true);
        if (array_key_exists('errors', $json)) {
            throw new \Exception('Failed to connect to SendGrid: '.$response->body());
        }
        $validDomains = array_filter($json, function ($domain) { return $domain['valid'] == true; });

        return array_map(function ($domain) { return $domain['domain']; }, $validDomains);
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        $response = $this->client()->client->whitelabel()->domains()->get();
        $json = json_decode($response->body(), true);
        if (array_key_exists('errors', $json)) {
            throw new \Exception('Failed to connect to SendGrid: '.$response->body());
        }

        return true;
    }
}
