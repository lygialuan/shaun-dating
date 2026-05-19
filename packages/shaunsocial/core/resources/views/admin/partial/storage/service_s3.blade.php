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
    <label class="control-label">{{__('Region')}}</label>
    <select id="region" name="region" class="form-select">
        @foreach($extra['regions'] as $key => $name)
            <option @if ($key == old('region',$config['region'])) selected @endif value="{{$key}}">{{$name}}</option>
        @endforeach
    </select>   
</div>
<div class="form-group">
    <label class="control-label">{{__('Bucket')}}</label>
    <input class="form-control" name="bucket" value="{{old('bucket',$config['bucket'])}}" type="text">
</div>
<div class="form-group">
    <label class="control-label">{{__('CloudFront Domain')}}</label>
    <input class="form-control" name="url" value="{{old('url',$config['url'])}}" type="text">
</div>