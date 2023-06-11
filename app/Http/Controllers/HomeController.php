<?php
namespace horsefly\Http\Controllers;

use Illuminate\Http\Request;
use horsefly\Model\Plan;
use horsefly\Cashier\Cashier;
use horsefly\Cashier\Services\StripeGatewayService;
use horsefly\Cashier\SubscriptionLog;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // event(new \horsefly\Events\UserUpdated($request->user()->customer));
        return view('frontend.index');
    }

    public function dashboard(Request $request)
    {
       
        event(new \horsefly\Events\UserUpdated($request->user()->customer));
        return view('dashboard');
    }
    
    public function services(Request $request)
    {
        // event(new \horsefly\Events\UserUpdated($request->user()->customer));
        return view('frontend.services');
    }
    
    public function contect(Request $request)
    {
        // event(new \horsefly\Events\UserUpdated($request->user()->customer));
        return view('frontend.contect');
    }
    
    public function aboutus(Request $request) {
        return view('frontend.about');
    }

    public function price(Request $request) {

        if(\Auth::user()) {
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();        
        $plans = Plan::getAvailablePlans();
        
        /*if ($request->isMethod('post')) {
            echo "Post";exit;
            $gateway->getChangePlanUrl($subscription, $plan->uid, action('AccountSubscriptionController@index'));
        }
        // Authorization
        if (!$request->user()->customer->can('changePlan', $subscription)) {
            return $this->notAuthorized();
        }*/

        return view('frontend.price', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plans' => $plans,
        ]);
    } else {
                
        $plans = Plan::getAvailablePlans(); 
        return view('frontend.price', [
            'plans' => $plans
        ]);
    }
    }

    public function makePayment(Request $request) {
        dd(route('subscription.new'));
        return redirect(route('subscription.new')); 
    }

    public function payment() {
        return redirect(route('subscription.new')); 
        return redirect(route('price'));
    }
}
