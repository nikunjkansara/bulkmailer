<?php

namespace horsefly\Cashier\Controllers;

use horsefly\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LaravelLog;
use horsefly\Cashier\Cashier;
use horsefly\Library\Facades\Billing;
use horsefly\Model\Setting;
use horsefly\Model\Invoice;
use horsefly\Library\TransactionVerificationResult;
use horsefly\Model\Transaction;
use horsefly\Library\AutoBillingData;
use horsefly\Cashier\Services\RazorpayPaymentGateway;

class RazorpayController extends Controller
{
    public function __construct()
    {
        \Carbon\Carbon::setToStringFormat('jS \o\f F');
    }

    public function settings(Request $request)
    {
        $gateway = $this->getPaymentService();

        if ($request->isMethod('post')) {
            // make validator
            $validator = \Validator::make($request->all(), [
                'key_id' => 'required',
                'key_secret' => 'required',
            ]);

            // test service
            $validator->after(function ($validator) use ($gateway, $request) {
                try {
                    $razorpay = new RazorpayPaymentGateway($request->key_id, $request->key_secret);
                    $razorpay->test();
                } catch(\Exception $e) {
                    $validator->errors()->add('field', 'Can not connect to ' . $gateway->getName() . '. Error: ' . $e->getMessage());
                }
            });

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('cashier::razorpay.settings', [
                    'gateway' => $gateway,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // save settings
            Setting::set('cashier.razorpay.key_id', $request->key_id);
            Setting::set('cashier.razorpay.key_secret', $request->key_secret);

            // enable if not validate
            if ($request->enable_gateway) {
                Billing::enablePaymentGateway($gateway->getType());
            }

            $request->session()->flash('alert-success', trans('cashier::messages.gateway.updated'));
            return redirect()->action('Admin\PaymentController@index');
        }

        return view('cashier::razorpay.settings', [
            'gateway' => $gateway,
        ]);
    }

    /**
     * Get current payment service.
     *
     * @return \Illuminate\Http\Response
     **/
    public function getPaymentService()
    {
        return Billing::getGateway('razorpay');
    }

    /**
     * Subscription checkout page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
    **/
    public function checkout(Request $request, $invoice_uid)
    {
        $customer = $request->user()->customer;
        $service = $this->getPaymentService();
        $invoice = Invoice::findByUid($invoice_uid);
        
        // Save return url
        if ($request->return_url) {
            $request->session()->put('checkout_return_url', $request->return_url);
        }

        // exceptions
        if (!$invoice->isNew()) {
            throw new \Exception('Invoice is not new');
        }

        // free plan. No charge
        if ($invoice->total() == 0) {
            $invoice->checkout($service, function($invoice) {
                return new TransactionVerificationResult(TransactionVerificationResult::RESULT_DONE);
            });

            return redirect()->action('SubscriptionController@index');
        }

        if ($request->isMethod('post')) {
            try {
                $service->charge($invoice, $request);
            } catch (\Exception $e) {    
                $request->session()->flash('alert-error', $e->getMessage());
                return redirect()->action('SubscriptionController@index');
            }

            // Redirect to my subscription page
            return redirect()->action('SubscriptionController@index');
        }

        // create order
        try {
            $order = $service->createRazorpayOrder($invoice);
            $customer = $service->getRazorpayCustomer($invoice);
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', $e->getMessage());
            return redirect()->action('SubscriptionController@index');
        }

        return view('cashier::razorpay.checkout', [
            'invoice' => $invoice,
            'service' => $service,
            'order' => $order,
            'customer' => $customer,
        ]);
    }
}
