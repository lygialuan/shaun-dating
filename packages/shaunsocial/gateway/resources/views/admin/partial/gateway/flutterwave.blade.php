<div class="form-group">
    <label class="control-label">{{__('Secret Key')}}</label>
    <input class="form-control" name="config[secret_key]" value="{{$config['secret_key'] ?? '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Public Key')}}</label>
    <input class="form-control" name="config[public_key]" value="{{$config['public_key'] ?? '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook signing secret')}}</label>
    <input class="form-control" name="config[signing_secret]" value="{{$config['signing_secret'] ?? '' }}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{ route('gateway.flutterwave.ipn') }}">
</div>
