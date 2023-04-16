<?php

namespace horsefly\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use horsefly\Model\User;
use horsefly\Model\Contact;

class ContactPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Contact $item)
    {
        return !isset($item->id) || $user->contact_id == $item->id;
    }
}
