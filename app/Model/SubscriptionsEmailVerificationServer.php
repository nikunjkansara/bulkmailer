<?php

namespace horsefly\Model;

use Illuminate\Database\Eloquent\Model;

class SubscriptionsEmailVerificationServer extends Model
{
    public function emailVerificationServer()
    {
        return $this->belongsTo('horsefly\Model\EmailVerificationServer', 'server_id');
    }
}
