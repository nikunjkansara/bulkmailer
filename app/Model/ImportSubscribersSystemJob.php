<?php

/**
 * ImportSubscribersSystemJob class, inherit from the SystemJob model.
 *
 * Model class for tracking subscriber importing system jobs.
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

class ImportSubscribersSystemJob extends SystemJob
{
    protected $table = 'system_jobs';

    public function getLog()
    {
        $data = json_decode($this->data, true);

        return $data['log'];
    }
}
