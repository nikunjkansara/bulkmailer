@extends('layouts.register') 

@section('title', trans('messages.login'))

@section('content')

    
    <div class="iframe-modal-body">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <div class="sub-section">
                
                    <h2>Price</h2>
                                    
                    @if (empty($plans))
                        <div class="row">
                            <div class="col-md-6">
                                @include('elements._notification', [
                                    'level' => 'danger',
                                    'message' => trans('messages.plan.no_available_plan')
                                ])
                            </div>
                        </div>
                    @else
                        <div class="price-box price-selectable">
                            <div class="price-table">
                        
                                @foreach ($plans as $plan)
                                <form name="frmPrice" method="post" action="{{route("price.post")}}" id="frmPrice">
                                {{ csrf_field() }}    
                                <div class="price-line current ">
                                        <div class="price-header">
                                            <lable class="plan-title">{{ $plan->name }}</lable>
                                            <p>{{ $plan->description }}</p>
                                        </div>
                                        
                                        <div class="price-item text-center">
                                            <div>{{ trans('messages.plan.starting_at') }}</div>
                                            <div class="plan-price">
                                                {{ \horsefly\Library\Tool::format_price($plan->price, $plan->currency->format) }}
                                            </div>
                                            <div>{{ $plan->displayFrequencyTime() }}</div>
                                            
                                            <input type="hidden" name="userId" value="{{isset($subscription->user_id)?$subscription->user_id:''}}"/>
                                            <input type="hidden" name="planId" value="{{$plan->uid}}"/>
                                            <input type="hidden" name="returnUrl" value="{{action('AccountSubscriptionController@index')}}" \>
                                            @if(!Auth::user())
                                            <button                                                 
                                                class="btn btn-mc_primary btn-mc_mk mt-30">
                                                    {{ trans('messages.plan.select') }}
                                            </button>
                                            @else
                                                @if ($subscription->plan->uid == $plan->uid)
                                                    <a
                                                        href="javascript:;"
                                                        class="btn btn-mc_default mt-30" disabled>
                                                            {{ trans('messages.plan.current_subscribed') }}
                                                    </a>
                                                @else
                                                    <a
                                                        href="{{ $gateway->getChangePlanUrl($subscription, $plan->uid, action('AccountSubscriptionController@index')) }}"
                                                        class="btn btn-mc_primary btn-mc_mk mt-30">
                                                            {{ trans('messages.plan.select') }}
                                                    </a>
                                                @endif
                                            @endif

                                        </div>
                                    </div>
                                </form>
                                @endforeach
                            
                            </div>
                        </div>
                    @endif
        
                </div>
            </div>
            <div class="col-md-1"></div>
        </div>
    </div>
    @endsection('content') 