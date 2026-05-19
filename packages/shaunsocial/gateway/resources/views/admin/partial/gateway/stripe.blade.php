<div class="form-group">
    <label class="control-label">{{__('Api secret key')}}</label>
    <input class="form-control" name="config[secret_key]" value="{{isset($config['secret_key']) ? $config['secret_key'] : '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Api publishable key')}}</label>
    <input class="form-control" name="config[publishable_key]" value="{{isset($config['publishable_key']) ? $config['publishable_key'] : '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook signing secret')}}</label>
    <input class="form-control" name="config[signing_secret]" value="{{isset($config['signing_secret']) ? $config['signing_secret'] : '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{route('gateway.stripe.ipn')}}">
</div>