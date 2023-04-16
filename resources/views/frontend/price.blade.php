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
                                            
                                           
                                                <a
                                                    href="#"
                                                    class="btn btn-mc_primary btn-mc_mk mt-30">
                                                        {{ trans('messages.plan.select') }}
                                                </a>
                                            
                                        </div>
                                    </div>
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