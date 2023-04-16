<?php

/**
 * SendingServerSendmail class.
 *
 * Abstract class for Sendmail sending server
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
use horsefly\Library\Log as MailLog;

class SendingServerSendmail extends SendingServer
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
            $transport = new \Swift_SendmailTransport($this->sendmail_path.' -bs');

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // Actually send
            $sent = $mailer->send($message);

            if ($sent) {
                MailLog::info('Sent!');

                return array(
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

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        if (!file_exists($this->sendmail_path)) {
            throw new \Exception("File {$this->sendmail_path} does not exists");
        }

        if (!is_executable($this->sendmail_path)) {
            throw new \Exception("File {$this->sendmail_path} is not executable");
        }

        return true;
    }
}
