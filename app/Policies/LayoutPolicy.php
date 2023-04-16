<?php

namespace horsefly\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use horsefly\Model\User;
use horsefly\Model\Layout;

class LayoutPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Layout $item)
    {
        $ability = $user->admin->getPermission('layout_update');
        $can = $ability == 'yes';

        return $can;
    }
}
