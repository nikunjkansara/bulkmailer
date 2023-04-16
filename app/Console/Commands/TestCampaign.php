<?php

namespace horsefly\Console\Commands;

use Illuminate\Console\Command;
use horsefly\Library\Log;
use horsefly\Library\QuotaTrackerStd;
use horsefly\Library\QuotaTrackerRedis;
use horsefly\Library\StringHelper;
use horsefly\Library\QuotaTracker;
use horsefly\Model\Campaign;
use horsefly\Model\User;
use horsefly\Model\MailList;
use horsefly\Model\Subscriber;
use horsefly\Model\TrackingLog;
use horsefly\Model\SendingServer;
use horsefly\Model\AutoTrigger;
use horsefly\Model\SendingServerElasticEmailApi;
use horsefly\Model\SendingServerElasticEmail;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Validator;

class TestCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->testImap();
    }

    public function testSmtp() {
        $transport = new \Swift_SmtpTransport('smtp.elasticemail.com', 2525, 'tls');
        $transport->setUsername('');
        $transport->setPassword('');
          ;

        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);

        // Create a message
        $message = new \Swift_Message('Wonderful Subject');
        $message->setFrom(array('' => 'Asish'));
        $message->setTo(array('' => 'Louis'));
        $message->setBody('Here is the message itself');

        // Send the message
        $result = $mailer->send($message);

        var_dump($result);
    }

    public function testImap() {
        // Connect to IMAP server
        $imapPath = "{mail.example.com:993/imap/tls}INBOX";

        // try to connect
        $inbox = imap_open($imapPath, 'user@example.com', 'password');

        // search and get unseen emails, function will return email ids
        $emails = imap_search($inbox, 'UNSEEN');

        if (!empty($emails)) {
            foreach ($emails as $message) {
                var_dump($message);
            }
        }

        // colse the connection
        imap_expunge($inbox);
        imap_close($inbox);
    }
}
