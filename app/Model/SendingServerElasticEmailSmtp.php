<?php

/**
 * SendingServerElasticEmailSmtp class.
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
use horsefly\Library\ExtendedSmtpTransport;

class SendingServerElasticEmailSmtp extends SendingServerElasticEmail
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
        try {
            $this->setupWebhooks();
            $this->enableCustomHeaders();

            $transport = ExtendedSmtpTransport::newInstance($this->host, (int) $this->smtp_port, $this->smtp_protocol);
            $transport->setUsername($this->smtp_username);
            $transport->setPassword($this->smtp_password);

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // Actually send
            $sent = $mailer->send($message);

            if ($sent && $transport->getMessageId()) {
                MailLog::info('Sent!');

                return array(
                    'runtime_message_id' => $transport->getMessageId(),
                    'status' => self::DELIVERY_STATUS_SENT,
                );
            } else {
                MailLog::warning('Sending failed');

                return array(
                    'status' => self::DELIVERY_STATUS_FAILED,
                    'error' => 'Unknown SMTP error',
                );
            }
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
