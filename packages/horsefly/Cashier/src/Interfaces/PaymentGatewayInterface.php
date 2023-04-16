<?php

namespace horsefly\Cashier\Interfaces;

use horsefly\Cashier\SubscriptionParam;
use horsefly\Cashier\Models\Subscription;

interface PaymentGatewayInterface
{
    public function create($customer, $plan);
    public function sync($subscription);
    public function validate();
    public function isSupportRecurring();
    public function useRemoteSubscription();
    public function hasPending($subscription);
    public function getPendingNotice($subscription);
    public function hasError($subscription);
    public function getErrorNotice($subscription);
    public function getChangePlanUrl($subscription, $plan_id, $returnUrl='/');
    public function getRenewUrl($subscription, $returnUrl='/');
}