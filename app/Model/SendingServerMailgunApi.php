<?php

/**
 * SendingServerMailgunApi class.
 *
 * Abstract class for Mailgun API sending server
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

class SendingServerMailgunApi extends SendingServerMailgun
{
    protected $table = 'sending_servers';

    /**
     * Send the provided message.
     *
     * @return bool
     *
     * @param message
     */
    public function send($message, $params = array())
    {
        try {
            $this->setupWebhooks();

            $fromEmail = array_keys($message->getFrom())[0];
            $toEmail = array_keys($message->getTo())[0];

            $result = $this->client()->sendMessage(
                $this->domain, array(
                    'from' => $fromEmail,
                    'to' => $toEmail,
                ),
                $message->toString()
            );

            MailLog::info('Sent!');

            return array(
                'runtime_message_id' => StringHelper::cleanupMessageId($result->http_response_body->id),
                'status' => self::DELIVERY_STATUS_SENT,
            );
        } catch (\Exception $e) {
            MailLog::warning('Sending failed');
            MailLog::warning($e->getMessage());

            return array(
                'status' => self::DELIVERY_STATUS_FAILED,
                'error' => $e->getMessage(),
            );
        }
    }
}
