<?php
namespace horsefly\Cashier\Facades;

use Illuminate\Support\Facades\Facade;
  
class PaymentGatewayFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'acellecashiergateway';
    }
}