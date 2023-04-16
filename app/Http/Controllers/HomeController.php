<?php
namespace horsefly\Http\Controllers;

use Illuminate\Http\Request;
use horsefly\Model\Plan;

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
        $plans = Plan::getAvailablePlans();
        
        return view('frontend.price', [
            'plans' => $plans
        ]);

        /*return view('account.subscription.change_plan', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plans' => $plans,
        ]);*/
    }
}
