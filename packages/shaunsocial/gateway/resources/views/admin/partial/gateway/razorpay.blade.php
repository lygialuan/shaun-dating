<div class="form-group">
    <label class="control-label">{{__('Key ID')}}</label>
    <input class="form-control" name="config[key_id]" value="{{ $config['key_id'] ?? '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Key Secret')}}</label>
    <input class="form-control" name="config[key_secret]" value="{{ $config['key_secret'] ?? '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook Secret')}}</label>
    <input class="form-control" name="config[webhook_secret]" value="{{ $config['webhook_secret'] ?? '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{route('gateway.razorpay.ipn')}}">
</div>
