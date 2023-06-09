@extends('layouts.backend')

@section('title', trans('messages.license'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
        </ul>
        <h1>
            <span class="text-gear"><i class="icon-key"></i> {{ trans('messages.license') }}</span>
        </h1>
    </div>

@endsection

@section('content')

    <form action="{{ action('Admin\SettingController@license') }}" method="POST" class="form-validate-jqueryz">
        {{ csrf_field() }}

        <div class="tabbable">
            @include("admin.settings._tabs")

            <div class="tab-content">

				@if ($license_error)
					<div class="alert alert-danger">
						{{ $license_error }}
					</div>
				@endif


                @foreach ($settings as $name => $setting)
                    @if (array_key_exists('cat', $setting) && $setting['cat'] == 'license')
                        @if ($current_license)
							<div class="sub-section">
								<h3>{{ trans('messages.license.your_license') }}</h3>
								<p>{{ trans('messages.your_current_license') }} <strong>{{ trans('messages.license_label_' . \horsefly\Model\Setting::get('license_type')) }}</strong></p>
								<h4>
									{{ $current_license }}
								</h4>
							</div>
                        @else
							<div class="sub-section">
								<h3>{{ trans('messages.license.your_license') }}</h3>
								<p> {{ trans('messages.license.no_license') }} </p>
							</div>
						@endif

						<div class="sub-section">
							<h3>{{ trans('messages.license.license_types') }}</h3>
							{!! trans('messages.license_guide') !!}
						</div>

						<div class="sub-section">
							@if (!$current_license)
								<h3>{{ trans('messages.verify_license') }}</h3>
							@else
								<h3>{{ trans('messages.change_license') }}</h3>
							@endif
							<div class="row license-line">
								<div class="col-md-6">
									@include('helpers.form_control', [
										'type' => $setting['type'],
										'class' => (isset($setting['class']) ? $setting['class'] : "" ),
										'name' => $name,
										'value' => (request()->license ? request()->license : ''),
										'label' => trans('messages.enter_license_and_click_verify'),
										'help_class' => 'setting',
										'options' => (isset($setting['options']) ? $setting['options'] : "" ),
										'rules' => horsefly\Model\Setting::rules(),
									])
								</div>
								<div class="col-md-6">
									<br />
									<div class="text-left">
										@if ($current_license)
											<button class="btn bg-teal"><i class="icon-check"></i> {{ trans('messages.change_license') }}</button>
										@else
											<button class="btn bg-teal"><i class="icon-check"></i> {{ trans('messages.verify_license') }}</button>
										@endif
									</div>
								</div>
							</div>
						</div>
                    @endif
                @endforeach
            </div>
        </div>


    </form>
@endsection
