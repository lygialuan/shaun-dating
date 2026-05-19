<div class="form-group">
    <label class="control-label">{{__('API Key')}}</label>
    <input class="form-control" name="config[api_key]" value="{{$config['api_key'] ?? '' }}" type="text" required>
</div>

<div class="form-group">
    <label class="control-label">{{__('API Secret')}}</label>
    <input class="form-control" name="config[api_secret]" value="{{$config['api_secret'] ?? '' }}" type="text" required>
</div>

<div class="form-group">
    <label class="control-label">{{__('Currency')}}</label>
    <input class="form-control" name="config[currency]" value="{{$config['currency'] ?? '' }}" type="text" placeholder="BTC" required>
</div>

<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="webhook_url" disabled type="text" value="{{route('gateway.binance.ipn')}}">
</div>
