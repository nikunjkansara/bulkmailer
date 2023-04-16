<?php

/**
 * Lockable class.
 *
 * Support concorrency-enabled classes
 *
 * LICENSE: This product includes software developed at
 * the HorseflyMailer. (http://horseflymailer.com/).
 *
 * @category   horsefly\
 *
 * @author     Nikunj K <nikunj@highvisiontech.com>
 * 
 * @copyright  HorseflyMailer
 * @license    HorseflyMailer
 *
 * @version    1.0
 *
 * @link       http://horseflymailer.com
 *
 * @todo separate the time-series and the quota stuffs
 */

namespace horsefly\Library;

class Lockable
{
    /**
     * Get exclusive lock.
     */
    private $file;

    public function __construct($file)
    {
        if (!file_exists($file)) {
            touch($file);
        }

        $this->file = $file;
    }

    /**
     * Get exclusive lock.
     */
    public function getExclusiveLock($callback, $timeout = 5, $exceptionCallback = null)
    {
        $start = time();
        $reader = fopen($this->file, 'w');
        while (true) {
            $this->checkTimeout($start, $timeout, $exceptionCallback);
            if (flock($reader, LOCK_EX | LOCK_NB)) {  // acquire an exclusive lock
                // execute the callback
                $callback($reader);

                fflush($reader);
                flock($reader, LOCK_UN);    // release the lock
                fclose($reader);
                break;
            }
        }
    }

    /**
     * Get shared lock.
     */
    public function getSharedLock($callback, $timeout = 5, $exceptionCallback = null)
    {
        $start = time();
        $reader = fopen($this->file, 'r');
        while (true) {
            $this->checkTimeout($start, $timeout, $exceptionCallback);
            if (flock($reader, LOCK_SH | LOCK_NB)) {  // acquire an exclusive lock
                // execute the callback
                $callback($this);

                flock($reader, LOCK_UN);    // release the lock
                fclose($reader);
                break;
            }
        }
    }

    /**
     * Check for timeout.
     */
    public function checkTimeout($startTime, $timeoutDuration, $callback)
    {
        if (time() - $startTime > $timeoutDuration) {
            if (!is_null($callback)) {
                $callback();
            } else {
                throw new \Exception('Timeout getting lock #Lockable');
            }
        }
    }
}
