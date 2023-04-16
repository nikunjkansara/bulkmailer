<?php

namespace horsefly\Cashier\Interfaces;

interface BillableUserInterface
{
    public function getBillableId();
    public function getBillableEmail();
}