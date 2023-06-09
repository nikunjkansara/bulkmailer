<html lang="en">
    <head>
        <title>{{ trans('cashier::messages.stripe.transaction.payment') }}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>            
        <link rel="stylesheet" href="{{ url('/vendor/acelle-cashier/css/main.css') }}">
    </head>
    
    <body>
        <div class="main-container row mt-40">
            <div class="col-md-2"></div>
            <div class="col-md-4 mt-40 pd-60">
                <label class="text-semibold text-muted mb-20 mt-0">
                    <strong>
                        {{ trans('cashier::messages.stripe.payment') }}
                    </strong>
                </label>
                <img class="rounded" width="100%" src="{{ url('/vendor/acelle-cashier/image/stripe.png') }}" />
            </div>
            <div class="col-md-4 mt-40 pd-60">                
                <label>{{ trans('cashier::messages.stripe.transaction.payment') }}</label>  
                <h2 class="mb-40">{{ $subscription->plan->getBillableName() }}</h2>

                <p>{!! trans('cashier::messages.stripe.fix_payment.intro', [
                    'plan' => $subscription->plan->getBillableName(),
                    'new_plan' => $subscription->plan->getBillableFormattedPrice(),
                ]) !!}</p>   
                
                <ul class="dotted-list topborder section mb-4">
                    <li>
                        <div class="unit size1of2">
                            {{ trans('cashier::messages.transaction.title') }}
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ $subscription->plan->getBillableName() }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            {{ trans('cashier::messages.stripe.next_period_day') }}
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ $subscription->nextPeriod()->format('d M, Y') }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            {{ trans('cashier::messages.stripe.amount') }}
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ $subscription->plan->getBillableFormattedPrice() }}</mc:flag>
                        </div>
                    </li>
                </ul>
                
                <div class="alert alert-danger">
                    {{ trans('cashier::messages.stripe.payment_outdated.alert') }}
                </div>

                <a href="javascript:;" class="btn btn-secondary full-width" onclick="$('#stripe_button button').click()">
                    {{ trans('cashier::messages.stripe.update_payment_and_proceed') }}
                </a>

                <form id="stripe_button" style="display: none" action="{{ action('\horsefly\Cashier\Controllers\StripeController@updateCard', [
                    '_token' => csrf_token(),
                    'subscription_id' => $subscription->uid,
                ]) }}" method="POST">
                    <input type="hidden" name="redirect" value="{{ action('\horsefly\Cashier\Controllers\StripeController@paymentRedirect', [
                        'redirect' => action('\horsefly\Cashier\Controllers\StripeController@fixPayment', [
                            'subscription_id' => $subscription->uid,
                        ]),
                    ]) }}" />

                    <script
                    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                    data-key="{{ $service->publishableKey }}"
                    data-amount="{{ $service->convertPrice($subscription->plan->getBillableAmount(), $subscription->plan->getBillableCurrency()) }}"
                    data-currency="{{ $subscription->plan->getBillableCurrency() }}"
                    data-name="{{ $subscription->plan->getBillableName() }}"
                    data-email="{{ $subscription->user->getBillableEmail() }}"
                    data-description="{{ $subscription->plan->description }}"
                    data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                    data-locale="{{ language_code() }}"
                    data-zip-code="true"
                    data-label="{{ trans('messages.pay_with_strip_label_button') }}">
                    </script>
                </form>

                <a
                    href="{{ action('AccountSubscriptionController@index') }}"
                    class="text-muted mt-4" style="text-decoration: underline; display: block"
                >{{ trans('cashier::messages.braintree.return_back') }}</a>
                
            </div>
            <div class="col-md-2"></div>
        </div>
        <br />
        <br />
        <br />
    </body>
</html>