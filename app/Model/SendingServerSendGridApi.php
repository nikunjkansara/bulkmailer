<?php

/**
 * SendingServerSendGridApi class.
 *
 * Abstract class for SendGrid API sending server
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

class SendingServerSendGridApi extends SendingServerSendGrid
{
    protected $table = 'sending_servers';

/**
 * Send the provided message.
 *
 * @return bool
 *
 * @param message
 */
    // Inherit class to implementation of this method
    public function send($message, $params = array())
    {
        $this->setupWebhooks();

        try {
            $msgId = $message->getHeaders()->get('X-Acelle-Message-Id')->getFieldBody();

            $mail = $this->prepareEmail($message);
            $response = $this->client()->client->mail()->send()->post($mail);
            $statusCode = $response->statusCode();

            # if response from SendGrid is 200, 202, 2xx
            if (preg_match('/^2../i', $statusCode)) {
                MailLog::info('Sent!');

                $result = array(
                    // @deprecated
                    // 'runtime_message_id' => StringHelper::cleanupMessageId($this->getMessageId($response->headers())),
                    'runtime_message_id' => $msgId,
                    'status' => self::DELIVERY_STATUS_SENT,
                );

                if (!is_null($this->subAccount)) {
                    $result['sub_account_id'] = $this->subAccount->id;
                }

                return $result;
            } else {
                throw new \Exception("{$statusCode} ".$response->body());
            }
        } catch (\Exception $e) {
            MailLog::warning('Sending failed');
            MailLog::warning($e->getMessage());

            $result = array(
                'status' => self::DELIVERY_STATUS_FAILED,
                'error' => $e->getMessage(),
            );

            if (!is_null($this->subAccount)) {
                $result['sub_account_id'] = $this->subAccount->id;
            }

            return $result;
        }
    }
}
