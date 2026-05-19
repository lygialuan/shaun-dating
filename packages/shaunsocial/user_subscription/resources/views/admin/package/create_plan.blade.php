<header class="modal-card-head">
    <p class="modal-card-title">{{ $title }}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="subscription_plan_form" method="post" enctype="multipart/form-data" action="{{ route('admin.user_subscription.plan.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="package_id" value="{{ $packageId }}" class="form-control"/>
            <input type="hidden" name="id" value="{{ $plan->id }}" class="form-control"/>
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($plan->id) readonly @endif value="{{$plan->getTranslatedAttributeValue('name') }}" type="text">
                @if ($plan->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$plan->getTable(),'name',$plan->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Price'). ' (' .getWalletTokenName(). ')'}}</label>
                <input class="form-control" name="amount" value="{{$plan->amount}}" type="number">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Trial (days)')}}</label>
                <input class="form-control" name="trial_day" value="{{$plan->trial_day}}" type="number">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Repeat every')}}</label>
                <input class="form-control mb-2" name="billing_cycle" value="{{$plan->billing_cycle}}" type="number">
                <select name="billing_cycle_type" class="form-select">
                    @foreach ($billingCycleType as $value => $billingCycleName)
                        <option @if (!empty($plan->billing_cycle_type) && $plan->billing_cycle_type->value == $value ) selected @endif value="{{$value}}">{{$billingCycleName}}</option>
                    @endforeach
                </select>
                <p class="help">
                    {{__('Note: auto-renew membership subscription will be notified to users in advance of :1 days.',['1'=> setting('shaun_user_subscription.remind_day')])}}
                </p>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Android Product Id')}}</label>
                <input class="form-control" name="google_price_id" value="{{$plan->google_price_id}}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('iOS Product Id')}}</label>
                <input class="form-control" name="apple_price_id" value="{{$plan->apple_price_id}}" type="text">
            </div>
            @if(count($gateways))
                @foreach ($gateways as $gateway)
                    @php
                        $selectedGateway = $plan->gateways->firstWhere('id', $gateway->id);
                    @endphp

                    <div class="mb-3">
                        <label>{{ $gateway->name }} Id</label>
                        <input type="text"
                            class="form-control mt-2"
                            name="gateways[{{ $gateway->id }}][flex_form_id]"
                            value="{{ $selectedGateway->pivot->flex_form_id ?? '' }}">
                    </div>

                @endforeach
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $plan->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
            @if ($plan->id)
            <p class="help">
                {{__('If you change package information here it only affects new subscribers. Old subscribers will not be affected.')}}
            </p>
            @endif
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="subscription_plan_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/user_subscription.js') }}"></script>
<script>
    adminUserSubscription.initCreatePlan();
</script>