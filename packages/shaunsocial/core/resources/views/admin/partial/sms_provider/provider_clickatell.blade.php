<div class="form-group">
    <label class="control-label">{{__('Api key')}}</label>
    <input class="form-control" name="config[api_key]" value="{{old('api_key',$config['api_key'] ?? '')}}" type="text">
</div>