<div class="modal-header">
    <a href="{{ action('Admin\PlanController@wizard', ['uid' => $plan->uid]) }}" class="mc-modal-back"><i class="icon-undo"></i></a>
    <h5 class="modal-title">{{ trans('messages.plan.new_plan') }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <!-- display flash message -->
	@include('common.errors')
    
    <div class="mc_section mb-0">
        <form id="wizard" enctype="multipart/form-data" action="{{ action('Admin\PlanController@wizardSendingServer', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
            {{ csrf_field() }}
            
            <div class="row">
                <div class="col-md-12">                    
                    <h2>{{ trans('messages.plan.sending_server') }}</h2>
                        
                    <p>{{ trans('messages.plan.sending_server.intro') }}</p>
                    
                    <div class="form-group control-radio">
                        <div class="radio_box" data-popup='tooltip' title="">
                            <label class="main-control">
                                <input {{ ($plan->getOption('sending_server_option') == \horsefly\Model\Plan::SENDING_SERVER_OPTION_SYSTEM ? 'checked' : '') }} type="radio"
                                    name="plan[options][sending_server_option]"
                                    value="{{ \horsefly\Model\Plan::SENDING_SERVER_OPTION_SYSTEM }}" class="styled" /><rtitle>{{ trans('messages.plan_option.system_s_sending_server') }}</rtitle>
                                <div class="desc text-normal mb-10">
                                    {{ trans('messages.plan_option.system_s_sending_server.intro') }}
                                </div>
                            </label>
                            <div class="radio_more_box">
                                
                            </div>
                        </div>
                        <hr>
                        <div class="radio_box" data-popup='tooltip' title="">
                            <label class="main-control">
                                <input {{ ($plan->getOption('sending_server_option') == \horsefly\Model\Plan::SENDING_SERVER_OPTION_OWN ? 'checked' : '') }} type="radio"
                                    name="plan[options][sending_server_option]"
                                    value="{{ \horsefly\Model\Plan::SENDING_SERVER_OPTION_OWN }}" class="styled" />
                                        <rtitle>{{ trans('messages.plan_option.own_sending_server') }}</rtitle>
                                <div class="desc text-normal mb-10">
                                    {{ trans('messages.plan_option.own_sending_server.intro') }}
                                </div>
                            </label>
                            <div class="radio_more_box">
                                <div class="boxing">
                                    @include('helpers.form_control', [
                                        'type' => 'text',
                                        'class' => 'numeric',
                                        'name' => 'plan[options][sending_servers_max]',
                                        'value' => $plan->getOption('sending_servers_max'),
                                        'label' => trans('messages.max_sending_servers'),
                                        'help_class' => 'plan',
                                        'options' => ['true', 'false'],
                                        'rules' => $plan->validationRules()['options'],
                                        'unlimited_check' => true,
                                    ])
                                </div>
                    
                                <p>
                                    @include('helpers.form_control', ['type' => 'checkbox2',
                                        'class' => '',
                                        'name' => 'plan[options][all_sending_server_types]',
                                        'value' => $plan->getOption('all_sending_server_types'),
                                        'label' => trans('messages.allow_adding_all_sending_server_types'),
                                        'options' => ['no','yes'],
                                        'help_class' => 'plan',
                                        'rules' => $plan->validationRules()['options'],
                                    ])
                                </p>
                                <div class="all_sending_server_types_no">
                                    <hr>
                                    <label class="text-semibold text-muted">{{ trans('messages.select_allowed_sending_server_types') }}</label>
                                    <div class="row">
                                        @foreach (horsefly\Model\SendingServer::types() as $key => $type)
                                            <div class="col-md-4 pt-10">
                                                &nbsp;&nbsp;<span class="text-semibold text-italic">{{ trans('messages.' . $key) }}</span>
                                                <span class="notoping pull-left">
                                                    @include('helpers.form_control', ['type' => 'checkbox',
                                                        'class' => '',
                                                        'name' => 'plan[options][sending_server_types][' . $key . ']',
                                                        'value' => isset($plan->getOption('sending_server_types')[$key]) ? $plan->getOption('sending_server_types')[$key] : 'no',
                                                        'label' => '',
                                                        'options' => ['no','yes'],
                                                        'help_class' => 'plan',
                                                        'rules' => $plan->validationRules()['options'],
                                                    ])
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="radio_box" data-popup='tooltip' title="">
                            <label class="main-control">
                                <input {{ ($plan->getOption('sending_server_option') == \horsefly\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT ? 'checked' : '') }} type="radio"
                                    name="plan[options][sending_server_option]"
                                    value="{{ \horsefly\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT }}" class="styled" /><rtitle>{{ trans('messages.plan_option.sub_account') }}</rtitle>
                                <div class="desc text-normal mb-10">
                                    {{ trans('messages.plan_option.sub_account.intro') }}
                                </div>
                            </label>
                            <div class="radio_more_box">
                                @if (Auth()->user()->admin->getSubaccountSendingServers()->count())
                                    <div class="row">
                                        <div class="col-md-6">
                                            @include('helpers.form_control', [
                                                'type' => 'select',
                                                'class' => 'numeric',
                                                'name' => 'plan[options][sending_server_subaccount_uid]',
                                                'value' => $plan->getOption('sending_server_subaccount_uid'),
                                                'label' => '',
                                                'help_class' => 'plan',
                                                'include_blank' => trans('messages.select_sending_server_with_subaccount'),
                                                'options' => Auth()->user()->admin->getSubaccountSendingServersSelectOptions(),
                                                'rules' => $plan->validationRules()['options'],
                                            ])
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-danger">
                                        {!! trans('messages.plan_option.there_no_subaccount_sending_server') !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <script>
            $(document).ready(function() {
                // all sending servers checking
                $(document).on("change", "input[name='plan[options][all_sending_servers]']", function(e) {
                    if($("input[name='plan[options][all_sending_servers]']:checked").length) {
                        $(".sending-servers").find("input[type=checkbox]").each(function() {
                            if($(this).is(":checked")) {
                                $(this).parents(".form-group").find(".switchery").eq(1).click();
                            }
                        });
                        $(".sending-servers").hide();
                    } else {
                        $(".sending-servers").show();
                    }
                });
                $("input[name='plan[options][all_sending_servers]']").trigger("change");
        
                // Sending domains checking setting
                $(document).on("change", "input[name='plan[options][create_sending_domains]']", function(e) {
                    if($('input[name="plan[options][create_sending_domains]"]:checked').val() == 'yes') {
                        $(".sending-domains-yes").show();
                        $(".sending-domains-no").hide();
                    } else {
                        $(".sending-domains-no").show();
                        $(".sending-domains-yes").hide();
                    }
                });
                $('input[name="plan[options][create_sending_domains]"]').trigger("change");
        
                // all email verification servers checking
                $(document).on("change", "input[name='plan[options][all_email_verification_servers]']", function(e) {
                    if($("input[name='plan[options][all_email_verification_servers]']:checked").length) {
                        $(".email-verification-servers").find("input[type=checkbox]").each(function() {
                            if($(this).is(":checked")) {
                                $(this).parents(".form-group").find(".switchery").eq(1).click();
                            }
                        });
                        $(".email-verification-servers").hide();
                    } else {
                        $(".email-verification-servers").show();
                    }
                });
                $("input[name='plan[options][all_email_verification_servers]']").trigger("change");
        
        
                // Email verification servers checking setting
                $(document).on("change", "input[name='plan[options][create_email_verification_servers]']", function(e) {
                    if($('input[name="plan[options][create_email_verification_servers]"]:checked').val() == 'yes') {
                        $(".email-verification-servers-yes").show();
                        $(".email-verification-servers-no").hide();
                    } else {
                        $(".email-verification-servers-no").show();
                        $(".email-verification-servers-yes").hide();
                    }
                });
                $('input[name="plan[options][create_email_verification_servers]"]').trigger("change");
        
                // Sending servers type checking setting
                $(document).on("change", "input[name='plan[options][all_sending_server_types]']", function(e) {
                    if($('input[name="plan[options][all_sending_server_types]"]:checked').val() == 'yes') {
                        $(".all_sending_server_types_yes").show();
                        $(".all_sending_server_types_no").hide();
                    } else {
                        $(".all_sending_server_types_no").show();
                        $(".all_sending_server_types_yes").hide();
                    }
                });
                $('input[name="plan[options][all_sending_server_types]"]').trigger("change");
            });
        </script>
    </div>
</div>
<div class="modal-footer text-center">
    <button onClick="$('#wizard').submit();" class="btn btn-mc_primary mr-10">{{ trans('messages.plan.wizard.finish') }}</button>
    <a href="javascript:;" class="btn btn-mc_inline mr-10" data-dismiss="modal">{{ trans('messages.plan.wizard.cancel') }}</a>
</div>
    
<script>
    $('#wizard').submit(function() {
        var form = $(this);
        
        // ajax load url
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            dataType: 'html',
        }).success(function(response) {
            if (response === 'success') {
                window.location = '{{ action('Admin\PlanController@general', $plan->uid) }}';                
            } else {
                mcModal.fill(response);
            }
        });
        
        return false;
    });
</script>
