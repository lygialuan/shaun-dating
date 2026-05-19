<div class="form-group">
    <label class="control-label">{{__('Sid')}}</label>
    <input class="form-control" name="config[sid]" value="{{old('sid',$config['sid'] ?? '')}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Token')}}</label>
    <div class="control">
        <input class="form-control" name="config[token]" value="{{old('token',$config['token'] ?? '')}}" type="text">
    </div>
</div>
<div class="form-group">
    <label class="control-label">{{__('From')}}</label>
    <input class="form-control" name="config[from]" value="{{old('from',$config['from'] ?? '')}}" type="text">
</div>