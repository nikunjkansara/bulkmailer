<?php
namespace horsefly\Http\Controllers;

use Illuminate\Http\Request;

class PlanController extends Controller
{

    /**
     * Customer subscription main page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check if system dosen't have payment gateway
        if (! Setting::get('system.payment_gateway')) {
            return view('noPrimaryPayment');
        }

        $customer = $request->user()->customer;
        $gateway = Cashier::getPaymentGateway();

        // Get current subscription
        $subscription = $customer->subscription;

        // Customer dose not have subscription
        if (! is_object($subscription) || $subscription->isEnded()) {
            $plans = Plan::getAvailablePlans();
            $planCount = Plan::getAllActive()->count();
            $colWidth = ($planCount == 0) ? 0 : round(85 / $planCount);
            return view('account.subscription.select_plan', [
                'plans' => $plans,
                'colWidth' => $colWidth,
                'subscription' => $subscription
            ]);
        }

        if (! $subscription->plan->isActive()) {
            return view('account.subscription.error', [
                'message' => __('messages.subscription.error.plan-not-active', [
                    'name' => $subscription->plan->name
                ])
            ]);
        }

        // Check if subscription is new
        if ($subscription->isNew()) {
            return redirect()->away($gateway->getCheckoutUrl($subscription, action('AccountSubscriptionController@index')));
        }

        // Check if subscription is new
        if ($subscription->isPending()) {
            return redirect()->away($gateway->getPendingUrl($subscription, action('AccountSubscriptionController@index')));
        }

        return view('account.subscription.index', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plan' => $subscription->plan
        ]);
    }

    /**
     * Select2 plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function select2(Request $request)
    {
        echo \horsefly\Model\Plan::select2($request);
    }
}
