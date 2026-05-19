<div class="form-group">
    <label class="control-label">{{__('Host')}}</label>
    <input class="form-control" name="redis_host" value="@isset($cacheSetting['redis_host']){{$cacheSetting['redis_host']}}@endisset" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Port')}}</label>
    <input class="form-control" name="redis_port" value="@isset($cacheSetting['redis_port']){{$cacheSetting['redis_port']}}@endisset" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Password')}}</label>
    <input class="form-control" name="redis_password" value="@isset($cacheSetting['redis_password']){{$cacheSetting['redis_password']}}@endisset" type="text">
</div>