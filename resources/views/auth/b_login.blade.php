@extends('layouts.register') 

@section('title', trans('messages.login'))

@section('content')
<!-- Advanced login -->
<form class="" role="form" method="POST" action="{{ url('/login') }}">
	{{ csrf_field() }}
	<div class="row mt-40 mc-form">
		<div class="col-md-4"></div>
		<div class="col-md-4">
			<div class="panel panel-body">

				<h4 class="text-semibold mt-0">{{ trans('messages.login') }}</h4>

				<div
					class="form-group has-feedback has-feedback-left{{ $errors->has('email') ? ' has-error' : '' }}">
					<input id="email" type="email" class="form-control" name="email"
						placeholder="{{ trans("
						messages.email") }}"
	 value="{{ old('email') ? old('email') : (isset(\horsefly\Model\User::getAuthenticateFromFile()['email']) ? \horsefly\Model\User::getAuthenticateFromFile()['email'] : "") }}"
            >
					<div class="form-control-feedback">
						<i class="icon-envelop5 text-muted"></i>
					</div>
					@if ($errors->has('email')) <span class="help-block"> <strong>{{
							$errors->first('email') }}</strong>
					</span> @endif
				</div>

				<div
					class="form-group has-feedback has-feedback-left{{ $errors->has('password') ? ' has-error' : '' }}">
					<input id="password" type="password" class="form-control"
						name="password" placeholder="{{ trans("
						messages.password") }}"
	value="{{ isset(\horsefly\Model\User::getAuthenticateFromFile()['password']) ? \horsefly\Model\User::getAuthenticateFromFile()['password'] : "" }}"
            >
					<div class="form-control-feedback">
						<i class="icon-lock2 text-muted"></i>
					</div>
					@if ($errors->has('password')) <span class="help-block"> <strong>{{
							$errors->first('password') }}</strong>
					</span> @endif
				</div>

				<div class="form-group login-options">
					<div class="row">
						<div class="col-sm-6">
							<label class="checkbox-inline"> <input type="checkbox"
								class="styled" checked="checked" name="remember"> {{
								trans("messages.stay_logged_in") }}
							</label>
						</div>

						<div class="col-sm-6 text-right text-semibold">
							<a href="{{ url('/password/reset') }}">{{
								trans("messages.forgot_password") }}</a>
						</div>
					</div>
				</div>

				@if (\horsefly\Model\Setting::get('login_recaptcha') == 'yes') {!!
				\horsefly\Library\Tool::showReCaptcha($errors) !!} @endif

				<button type="submit" class="btn btn-lg bg-teal btn-block">
					{{ trans("messages.login") }} <i
						class="icon-circle-right2 position-right"></i>
				</button>
			</div>
			@if (\horsefly\Model\Setting::get('enable_user_registration') ==
			'yes')
			<div class="text-center">{!!
				trans('messages.need_a_account_create_an_one', [ 'link' =>
				action('UserController@register') ]) !!}</div>
			@endif
		</div>
		<div class="col-md-4"></div>
	</div>

</form>
<!-- /advanced login -->

@endsection
