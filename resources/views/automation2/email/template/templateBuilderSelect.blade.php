@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2>{{ trans('messages.automation.template.builder.select') }}</h2>
            <p>{{ trans('messages.automation.template.builder.select.intro') }}</p>
            
            <a href="{{ action('Automation2Controller@templateEdit', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]) }}" class="btn btn-secondary mr-1 template-compose"
            >
                {{ trans('messages.campaign.email_builder_pro') }}
            </a>
            <a href="{{ action('Automation2Controller@templateEditClassic', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]) }}" class="btn btn-outline-secondary mr-1 template-compose-classic"
            >
                {{ trans('messages.campaign.email_builder_classic') }}
            </a>
        </div>
    </div>
    <script>
        $('.template-compose').click(function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');

            openBuilder(url);

            builderSelectPopup.hide();
        });
        
        $('.template-compose-classic').click(function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');

            openBuilderClassic(url);

            builderSelectPopup.hide();
        });
    </script>
@endsection