<?php

namespace horsefly\Model;

use Illuminate\Database\Eloquent\Model;

class PlansEmailVerificationServer extends Model
{
    // Plan status
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';

    public function emailVerificationServer()
    {
        return $this->belongsTo('horsefly\Model\EmailVerificationServer', 'server_id');
    }
}
