@if ($subscription->plan->price == 0)
	Payment was done. Redirecting to subscription page...
	<script>
		window.location = '{{ action('AccountSubscriptionController@index') }}';
	</script>
@else
	@php
		$metadata = $subscription->getMetadata();
        $transactions = isset($metadata->transactions) ? $metadata->transactions : [];

        $tid = 'Transaction ID: ' . uniqid();

        $transactions[] = [
            'id' => $tid,
            'createdAt' => $subscription->created_at->timestamp,
			'periodEndsAt' => $subscription->ends_at->timestamp,
			'amount' => \horsefly\Library\Tool::format_price($subscription->plan->price, $subscription->plan->currency->format),
        ];

        $subscription->updateMetadata(['transactions' => $transactions]);
	@endphp
	<form action="https://www.coinpayments.net/index.php" method="post">
		<input type="hidden" name="cmd" value="_pay_simple">
		<input type="hidden" name="reset" value="1">
		<input type="hidden" name="merchant" value="{{ $gateway['fields']['merchant_id'] }}">
		<input type="hidden" name="currency" value="{{ $subscription->plan->currency->code }}">
		<input type="hidden" name="amountf" value="{{ $subscription->plan->price }}">
		<input type="hidden" name="item_name" value="{{ trans('messages.invoice.subscribe_to_plan', [
			'plan' => $subscription->plan->name,
		]) }}">
		<input type="hidden" name="item_number" value="{{ $subscription->uid }}">
		<input type="hidden" name="item_desc" value="{{ $tid }}">
		<input type="hidden" name="success_url" value="{{ action('AccountSubscriptionController@index') }}">
		<input type="image" src="https://www.coinpayments.net/images/pub/CP-main-large.png" alt="Buy Now with CoinPayments.net">
	</form>
@endif