@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="login-form-wrap">
    <div class="login-form-box">
        <div class="login-form-logo">
            <a href="{{ route('web.home.index')}}">
                <img src="{{setting('site.logo')}}" style="max-width: 200px"/>
            </a>
        </div>
        <div class="main-login-form">
            @include('shaun_core::admin.partial.error')
            <form method="post" action="{{ route('admin.auth.login')}}">
                {{ csrf_field() }}
                <input type="hidden" name="redirect" value="{{$redirect}}"/>
                <input type="hidden" name="token" id="token" />
                <div class="form-group mb-3">
                    <div class="form-control-has-icon icon-l">
                        <span class="form-control-icon form-control-icon-prepend material-symbols-outlined notranslate"> mail </span>
                        <input class="form-control" type="text" name="email" placeholder="{{__('Email')}}" value="{{old('email' ,env('ADMIN_EMAIL_DEFAULT',''))}}" autocomplete="username">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <div class="form-control-has-icon icon-x">
                        <span class="form-control-icon form-control-icon-prepend material-symbols-outlined notranslate"> key </span>
                        <input class="form-control" id="formControlPassword" type="password" name="password" placeholder="{{__('Password')}}" value="{{old('password',env('ADMIN_PASSWORD_DEFAULT',''))}}" autocomplete="current-password">
                        <a href="javascript:void(0);" id="showHiddenPassword" class="form-control-icon form-control-icon-append material-symbols-outlined notranslate"> visibility_off </a>
                    </div>
                </div>
                <button type="submit" class="btn-filled-blue w-100">
                    {{__('Login')}}
                </button>
                <a href="{{ route('web.user.recover')}}" class="d-block mt-4">{{__('Forgot password')}}</a>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts-body')
<script>
    adminCore.showHiddenPassword();
</script>
@endpush