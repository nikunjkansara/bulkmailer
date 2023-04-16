<?php

namespace horsefly\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use horsefly\Model\User;
use horsefly\Model\Setting;

class SettingPolicy
{
    use HandlesAuthorization;
    
    public function general(User $user, Setting $item)
    {
        $can = $user->admin->getPermission('setting_general') == 'yes';

        return $can;
    }
    
    public function sending(User $user, Setting $item)
    {
        $can = $user->admin->getPermission('setting_sending') == 'yes';

        return $can;
    }
    
    public function system_urls(User $user, Setting $item)
    {
        $can = $user->admin->getPermission('setting_system_urls') == 'yes';

        return $can;
    }
    
    public function access_when_offline(User $user, Setting $item)
    {
        $can = $user->admin->getPermission('setting_access_when_offline') == 'yes';

        return $can;
    }
}
