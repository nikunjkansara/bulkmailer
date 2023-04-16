<?php

namespace horsefly\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use horsefly\Model\User;
use horsefly\Model\SystemJob;

class SystemJobPolicy
{
    use HandlesAuthorization;
    
    public $jobs = [
        'horsefly\Jobs\ImportSubscribersJob',
        'horsefly\Jobs\ExportSubscribersJob',
        'horsefly\Jobs\ExportSegmentsJob',
    ];

    public function delete(User $user, SystemJob $item)
    {
        if (in_array($item->name, $this->jobs)) {
            $data = json_decode($item->data);
            $list = \horsefly\Model\MailList::findByUid($data->mail_list_uid);

            return $list->customer_id == $user->customer->id && !$item->isRunning();
        }

        return false;
    }

    public function downloadImportLog(User $user, SystemJob $item)
    {
        $data = json_decode($item->data);
        $list = \horsefly\Model\MailList::findByUid($data->mail_list_uid);

        return $list->customer_id == $user->customer->id &&
            $item->name == 'horsefly\Jobs\ImportSubscribersJob' &&
            $data->status == 'done';
    }

    public function downloadExportCsv(User $user, SystemJob $item)
    {
        $data = json_decode($item->data);
        $list = \horsefly\Model\MailList::findByUid($data->mail_list_uid);

        return $list->customer_id == $user->customer->id &&
            ($item->name == 'horsefly\Jobs\ExportSubscribersJob' || $item->name == 'horsefly\Jobs\ExportSegmentsJob') &&
            $data->status == 'done';
    }

    public function cancel(User $user, SystemJob $item)
    {
        if (in_array($item->name, $this->jobs)) {
            $data = json_decode($item->data);
            $list = \horsefly\Model\MailList::findByUid($data->mail_list_uid);

            return $list->customer_id == $user->customer->id &&
                ($item->isRunning() || $item->isNew());
        }

        return false;
    }
}
