<?php

/**
 * SendingServerAmazonApi class.
 *
 * Model class for Amazon API sending server
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

class SendingServerAmazonApi extends SendingServerAmazon
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
        $this->setupSnsThreadSafe($message);
        try {
            $sent = $this->sesClient()->sendRawEmail(array(
                'RawMessage' => array(
                    'Data' => $message->toString(),
                ),
            ));

            MailLog::info('Sent!');

            return array(
                'runtime_message_id' => $sent['MessageId'],
                'status' => self::DELIVERY_STATUS_SENT,
            );
        } catch (\Exception $e) {
            $this->raiseSendingError($e);

            return array(
                'status' => self::DELIVERY_STATUS_FAILED,
                'error' => $e->getMessage(),
            );
        }
    }
}
