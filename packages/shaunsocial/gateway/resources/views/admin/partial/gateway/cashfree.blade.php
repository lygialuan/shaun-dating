<div class="form-group">
    <label class="control-label">{{__('App ID (Client ID)')}}</label>
    <input class="form-control" name="config[public_key]" value="{{$config['public_key'] ?? '' }}" type="text" placeholder="e.g., 12345abcde">
</div>

<div class="form-group">
    <label class="control-label">{{__('Secret Key')}}</label>
    <input class="form-control" name="config[secret_key]" value="{{$config['secret_key'] ?? '' }}" type="text" placeholder="e.g., abcd1234secret">
</div>

<div class="form-group">
    <label class="control-label">{{__('Webhook Signing Secret')}}</label>
    <input class="form-control" name="config[signing_secret]" value="{{$config['signing_secret'] ?? '' }}" type="text" placeholder="Optional - Used for IPN validation">
</div>

<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="webhook_url" disabled type="text" value="{{ route('gateway.cashfree.ipn') }}">
</div>

<div class="form-group">
    <label class="control-label">{{__('Sandbox Mode')}}</label>
    <div class="form-check form-switch">
        <input name="config[sandbox]" class="form-check-input" type="checkbox" {{ !empty($config['sandbox']) ? 'checked' : '' }} value="1">
    </div>
</div>
