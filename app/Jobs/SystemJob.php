<?php

/**
 * SystemJob class.
 *
 * The abstract class for Laravel Jobs
 * All jobs created shall extend this
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

namespace horsefly\Jobs;

use horsefly\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use horsefly\Model\SystemJob as SystemJobModel;

class SystemJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $systemJob;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // init the SystemJob record to keep track of
        $systemJob = SystemJobModel::create(array(
            'status' => SystemJobModel::STATUS_NEW,
            'name' => get_called_class(),
        ));
        
        $this->systemJob = $systemJob;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function getSystemJob() {
        return SystemJobModel::find($this->systemJob->id);
        // @todo: what if job cannot be found?
    }

    /**
     * Cark the SystemJob record as RUNNING
     *
     * @return void
     */
    public function setStarted() {
        $systemJob = $this->getSystemJob();
        $systemJob->setStarted();
    }

    /**
     * Cark the SystemJob record as DONE
     *
     * @return void
     */
    public function setDone() {
        $systemJob = $this->getSystemJob();
        $systemJob->setDone();
    }

    /**
     * Mark the SystemJob record as FAILED
     *
     * @return void
     */
    public function setFailed() {
        $systemJob = $this->getSystemJob();
        $systemJob->setFailed();
    }
}
