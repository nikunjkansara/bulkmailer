<?php

namespace horsefly\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use horsefly\Model\User;
use horsefly\Model\Campaign;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function read(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id;
    }

    public function create(User $user, Campaign $item)
    {
        $customer = $user->customer;
        $max = $customer->getOption('campaign_max');
        
        return $max > $customer->campaigns()->count() || $max == -1;
    }

    public function overview(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id && $item->status != 'new';
    }

    public function update(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id
            && ($item->is_auto || in_array($item->status, ['new', 'ready', 'error']));
    }

    public function delete(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id && in_array($item->status, ['new', 'ready', 'paused', 'done', 'sending', 'error']);
    }

    public function pause(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id && in_array($item->status, ['sending', 'ready']);
    }

    public function restart(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id && in_array($item->status, ['paused', 'error']);
    }

    public function sort(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id;
    }

    public function copy(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id;
    }

    public function preview(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id;
    }

    public function saveImage(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id;
    }

    public function image(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id;
    }

    public function resend(User $user, Campaign $item)
    {
        $customer = $user->customer;
        return $item->customer_id == $customer->id && $item->isDone();
    }
}
