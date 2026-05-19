<div class="form-group">
    <label class="control-label">{{ __('API Key') }}</label>
    <input class="form-control" name="config[api_key]" value="{{ $config['api_key'] ?? '' }}" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{ __('IPN Secret (optional)') }}</label>
    <input class="form-control" name="config[ipn_secret]" value="{{ $config['ipn_secret'] ?? '' }}" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Webhook URL') }}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{ route('gateway.nowpayments.ipn') }}">
</div>
