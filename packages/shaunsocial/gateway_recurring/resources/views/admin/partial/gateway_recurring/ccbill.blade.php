<div class="form-group">
    <label class="control-label">{{__('Client Account Number')}}</label>
    <input class="form-control" name="config[client_account_number]" value="{{$config['client_account_number'] ?? ''}}" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Sub Account Number')}}</label>
    <input class="form-control" name="config[sub_account_number]" value="{{$config['sub_account_number'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Salt')}}</label>
    <input class="form-control" name="config[salt]" value="{{$config['salt'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Datalink Username')}}</label>
    <input class="form-control" name="config[datalink_username]" value="{{$config['datalink_username'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Datalink Password')}}</label>
    <input class="form-control" name="config[datalink_password]" value="{{$config['datalink_password'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{route('gateway_recurring.ccbill.ipn')}}">
</div>
