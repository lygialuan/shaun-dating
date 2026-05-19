<div class="form-group">
    <label class="control-label">{{__('Host')}}</label>
    <input class="form-control" name="memcached_host" value="@isset($cacheSetting['memcached_host']){{$cacheSetting['memcached_host']}}@endisset" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Port')}}</label>
    <input class="form-control" name="memcached_port" value="@isset($cacheSetting['memcached_port']){{$cacheSetting['memcached_port']}}@endisset" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Username')}}</label>
    <input class="form-control" name="memcached_username" value="@isset($cacheSetting['memcached_username']){{$cacheSetting['memcached_username']}}@endisset" type="text">
</div>

<div class="form-group">
    <label class="control-label">{{__('Password')}}</label>
    <input class="form-control" name="memcached_password" value="@isset($cacheSetting['memcached_password']){{$cacheSetting['memcached_password']}}@endisset" type="text">
</div>