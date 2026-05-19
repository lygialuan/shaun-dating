<div class="form-group">
    <label class="control-label">{{__('Paypal client id')}}</label>
    <input class="form-control" name="config[client_id]" value="{{isset($config['client_id']) ? $config['client_id'] : '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Paypal secret')}}</label>
    <input class="form-control" name="config[secret]" value="{{isset($config['secret']) ? $config['secret'] : '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Paypal webhook id')}}</label>
    <input class="form-control" name="config[webhook]" value="{{isset($config['webhook']) ? $config['webhook'] : '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Sandbox')}}</label>            
    <div class="form-check form-switch">
        <input  name="config[sandbox]" class="form-check-input" type="checkbox" {{ !empty($config['sandbox']) ? 'checked' : ''}} value="1">
    </div>
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{route('gateway.paypal.ipn')}}">
</div>