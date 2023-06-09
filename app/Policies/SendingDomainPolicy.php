<?php

namespace horsefly\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use horsefly\Model\User;
use horsefly\Model\SendingDomain;
use horsefly\Model\Plan;

class SendingDomainPolicy
{
    use HandlesAuthorization;

    public function read(User $user, SendingDomain $item, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('sending_domain_read') != 'no';
                break;
            case 'customer':
                $subscription = $user->customer->subscription;

                if ($subscription->plan->useOwnSendingServer()) {
                    $can = true;
                } else {
                    $server = $subscription->plan->primarySendingServer();
                    $can = $server->allowVerifyingOwnDomains();    
                }

                break;
        }

        return $can;
    }

    public function readAll(User $user, SendingDomain $item, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('sending_domain_read') == 'all';
                break;
            case 'customer':
                $can = false;
                break;
        }

        return $can;
    }

    public function create(User $user, SendingDomain $item, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('sending_domain_create') == 'yes';
                break;
            case 'customer':
                $subscription = $user->customer->subscription;

                if ($subscription->plan->useOwnSendingServer()) {
                    $can = true;
                } else {
                    $server = $subscription->plan->primarySendingServer();
                    $can = $server->allowVerifyingOwnDomains();    
                }
                break;
        }

        return $can;
    }

    public function update(User $user, SendingDomain $item, $role)
    {
        switch ($role) {
            case 'admin':
                $ability = $user->admin->getPermission('sending_domain_update');
                $can = $ability == 'all'
                    || ($ability == 'own' && $user->admin->id == $item->admin_id);
                break;
            case 'customer':
                $subscription = $user->customer->subscription;

                if ($subscription->plan->useOwnSendingServer()) {
                    $can = true;
                } else {
                    $server = $subscription->plan->primarySendingServer();
                    $can = $server->allowVerifyingOwnDomains();    
                }

                $can = $can && $user->customer->id == $item->customer_id;
                break;
        }

        return $can;
    }

    public function delete(User $user, SendingDomain $item, $role)
    {
        switch ($role) {
            case 'admin':
                $ability = $user->admin->getPermission('sending_domain_delete');
                $can = $ability == 'all'
                    || ($ability == 'own' && $user->admin->id == $item->admin_id);
                break;
            case 'customer':
                $subscription = $user->customer->subscription;

                if ($subscription->plan->useOwnSendingServer()) {
                    $can = true;
                } else {
                    $server = $subscription->plan->primarySendingServer();
                    $can = $server->allowVerifyingOwnDomains();    
                }
                
                $can = $can && $user->customer->id == $item->customer_id;
                break;
        }

        return $can;
    }
}
