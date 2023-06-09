<?php

namespace horsefly\Library\Automation;

use horsefly\Model\Email;

class Evaluate extends Action
{
    protected $childYes;
    protected $childNo;

    public function __construct($params = [])
    {
        parent::__construct($params);

        $this->childYes = array_key_exists('childYes', $params) ? $params['childYes'] : null;
        $this->childNo = array_key_exists('childNo', $params) ? $params['childNo'] : null;
    }

    public function toJson()
    {
        $json = parent::toJson();
        $json = array_merge($json, [
            'childYes' => $this->childYes,
            'childNo' => $this->childNo,
        ]);

        return $json;
    }

    public function execute()
    {
        try {
            // IMPORTANT
            // If this is the latest also the last action of the workflow
            // no more execute, just return true
            // UPDATE: check always, wait for open/click anyway! if it is the last action
            // if (!is_null($this->last_executed)) {
            //     $this->autoTrigger->logger()->info('Latest also last action');
            //     return true;
            // }

            $result = $this->evaluateCondition();

            if (config('app.demo') == true) {
                $result = (bool) random_int(0, 1);
            }

            $this->evaluationResult = $result;

            $this->recordLastExecutedTime();

            // always return true, not evaluation result!
            return true;
        } catch (\Exception $ex) {
            $this->autoTrigger->logger()->warning(sprintf('Error while executing Condition %s. Error message: %s', $this->getId(), $ex->getMessage()));

            return false;
        }
    }

    public function evaluateCondition()
    {
        $criterion = $this->getOption('type');
        $result = null;

        switch ($criterion) {
            case 'open':
                if (empty($this->getOption('email'))) {
                    throw new \Exception('Email missing for open condition');
                }
                $result = $this->evaluateEmailOpenCondition();
                break;
            case 'click':
                if (empty($this->getOption('email_link'))) {
                    throw new \Exception('URL missing for click condition');
                }
                $result = $this->evaluateEmailOpenCondition();
                break;
            default:
                # code...
                break;
        }

        return $result;
    }

    public function evaluateEmailOpenCondition()
    {
        $emailUid = $this->getOption('email');
        $email = Email::findByUid($emailUid);

        return $email->isOpened($this->autoTrigger->subscriber);
    }

    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();
        $emailUid = $this->getOption('email');
        $email = Email::findByUid($emailUid);

        if ($this->getOption('type') == 'open') {
            return sprintf('Tracking: waiting for user %s to READ email entitled "%s"', $nameOrEmail, $email->subject);
        } else {
            return sprintf('Tracking: waiting for user %s to CLICK email entitled "%s"', $nameOrEmail, $email->subject);
        }
    }

    public function hasChild($e)
    {
        if (is_null($this->childYes) && is_null($this->childNo)) {
            return false;
        }

        return $e->getId() == $this->childYes || $e->getId() == $this->childNo;
    }

    public function getNextActionId()
    {
        if ($this->evaluationResult) {
            return $this->childYes;
        } else {
            return $this->childNo;
        }
    }
}
