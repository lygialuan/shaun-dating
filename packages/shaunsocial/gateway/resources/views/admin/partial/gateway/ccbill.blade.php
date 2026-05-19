<div class="form-group">
    <label class="control-label">{{__('Client Account Number')}}</label>
    <input class="form-control" name="config[client_account_number]" value="{{$config['client_account_number'] ?? ''}}" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Sub Account Number')}}</label>
    <input class="form-control" name="config[sub_account_number]" value="{{$config['sub_account_number'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('FlexForm ID')}}</label>
    <input class="form-control" name="config[flex_form_id]" value="{{$config['flex_form_id'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Salt')}}</label>
    <input class="form-control" name="config[salt]" value="{{$config['salt'] ?? ''}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Webhook URL')}}</label>
    <input class="form-control" id="return_url" disabled type="text" value="{{route('gateway.ccbill.ipn')}}">
</div>
