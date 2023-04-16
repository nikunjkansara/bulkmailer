<p>{!! trans('messages.subscription_done.stripe.email', [
    'customer' => $customerName,
    'plan' => $planName,
    'link' => $link,
]) !!}</p>

--<br>
{{ \horsefly\Model\Setting::get('site_name') }}
