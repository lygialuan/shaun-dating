<div class="form-group">
    <label class="control-label">{{__('Access Key')}}</label>
    <input class="form-control" name="key" value="{{old('key',$config['key'])}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Secret Key')}}</label>
    <div class="control">
        <input class="form-control" name="secret" value="{{old('secret',$config['secret'])}}" type="text">
    </div>
</div>
<div class="form-group">
    <label class="control-label">{{__('Bucket')}}</label>
    <input class="form-control" name="bucket" value="{{old('bucket',$config['bucket'])}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('Endpoint')}}</label>
    <input class="form-control" name="endpoint" value="{{old('endpoint',$config['endpoint'])}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('CDN')}}</label>
    <input class="form-control" name="url" value="{{old('url',$config['url'])}}" type="text">
</div>