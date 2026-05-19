@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">
            @if ($provider->id)
                {{__('Edit OpenID')}}
            @else
                {{__('Create New OpenID')}}
            @endif
        </h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="openid_form" method="post" action="{{ route('admin.openid.store')}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" value="{{ $provider->id }}" />

            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{old('name',$provider->name)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('App name')}}</label>
                <input class="form-control" name="app_name" id="app_name" value="{{old('app_name',$provider->app_name)}}" @if ($provider->is_core) readonly @endif type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Return URL')}}</label>
                <input class="form-control" id="return_url" disabled type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Return App URL')}}</label>
                <input class="form-control" id="return_app_url" disabled type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Delete user URL')}}</label>
                <input class="form-control" id="delete_user_url" disabled type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Logo')}}</label>
                <input type="file" name="photo" class="form-control">
                @php 
                    $photo = $provider->getPhoto();
                @endphp
                @if ($photo)
                    <div class="img_settings_container">
                        <img class="img-fluid" src="{{ $photo }}">
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Server')}}</label>
                <input class="form-control" name="server" value="{{old('server',$provider->server)}}" type="text">
            </div>
            
            <div class="form-group">
                <label class="control-label">{{__('Client ID')}}</label>
                <input class="form-control" name="client_id" value="{{old('client_id',$provider->client_id)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Client secret')}}</label>
                <input class="form-control" name="client_secret" value="{{old('client_secret',$provider->client_secret)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Scope')}}</label>
                <input class="form-control" name="scope" value="{{old('scope',$provider->scope)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Authorize endpoint')}}</label>
                <input class="form-control" name="authorize_endpoint" value="{{old('authorize_endpoint',$provider->authorize_endpoint)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Access token endpoint')}}</label>
                <input class="form-control" name="access_token_endpoint" value="{{old('access_token_endpoint',$provider->access_token_endpoint)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Get UserInfo endpoint')}}</label>
                <input class="form-control" name="get_user_info_endpoint" value="{{old('get_user_info_endpoint',$provider->get_user_info_endpoint)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('User ID map')}}</label>
                <input class="form-control" name="user_id_map" value="{{old('user_id_map',$provider->user_id_map)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Email map')}}</label>
                <input class="form-control" name="email_map" value="{{old('email_map',$provider->email_map)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Name map')}}</label>
                <input class="form-control" name="name_map" value="{{old('name_map',$provider->name_map)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Avatar map')}}</label>
                <input class="form-control" name="avatar_map" value="{{old('avatar_map',$provider->avatar_map)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>
                <div class="form-check form-switch">
                    <input id="is_active" class="form-check-input" name="is_active" type="checkbox" {{old('is_active',$provider->is_active) ? 'checked' : ''}} value="1">
                </div>            
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-filled-blue">
                    {{__('Submit')}}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script src="{{ asset('admin/js/open_provider.js') }}"></script>
<script>
    adminOpenProvider.initCreateOpenProvider('{{route('web.openid.auth',['name' =>'123'])}}', '{{route('web.openid.delete_user',['name' =>'123'])}}')
</script>
@endpush