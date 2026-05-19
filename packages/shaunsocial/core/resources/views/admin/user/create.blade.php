@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">
            @if ($user->id)
                {{__('Edit User')}}
            @else
                {{__('Create New User')}}
            @endif
            
        </h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="user_form" method="post" action="{{ route('admin.user.store')}}">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" value="{{ $user->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{old('name',$user->name)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Email')}}</label>
                <input class="form-control" name="email" value="{{old('email',$user->email)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Username')}}</label>
                <input class="form-control" name="user_name" value="{{old('user_name',$user->user_name)}}" type="text">
            </div>

            @if (! $user->id)
                <div class="form-group">
                    <label class="control-label">{{__('Password')}}</label>
                    <input class="form-control" name="password" value="" type="password">
                </div>
            @elseif ($user->canActionOnAdminPanel(auth()->user()))
                <div class="form-group">
                    <label class="control-label">{{__('Password')}}</label>
                    <div>
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.user.change_password',$user->id)}}">{{__('Change password')}}</a>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label class="control-label">{{__('Birthday')}}</label>
                <input class="form-control" name="birthday" value="{{old('birthday',$user->birthday)}}" type="date">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Timezone')}}</label>            
                <select name="timezone" class="form-select">
                    @foreach($timezones as $id => $name)
                        <option @if (old('timezone',$user->timezone) == $id) selected @endif value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>             
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Bio')}}</label>
                <textarea class="form-control" name="bio">{{old('bio',$user->bio)}}</textarea>
            </div>
            
            <div class="form-group">
                <label class="control-label">{{__('About')}}</label>
                <textarea class="form-control" name="about">{{old('about',$user->about)}}</textarea>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Location')}}</label>
                <input class="form-control" name="location" value="{{old('location',$user->location)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Gender')}}</label>            
                <select name="gender_id" class="form-select">
                    <option value="0">{{__('Prefer not to say')}}</option>
                    @foreach($genders as $id => $name)
                        <option @if (old('gender_id',$user->gender_id) == $id) selected @endif value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>             
            </div>
            @if ($user->id != config('shaun_core.core.user_root_id')) 
                <div class="form-group">
                    <label class="control-label">{{__('Role')}}</label>            
                    <select name="role_id" class="form-select">
                        @foreach($roles as $role)
                            <option @if (old('role_id',$user->role_id) == $role->id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                        @endforeach
                    </select>             
                </div>
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" id="is_active" type="checkbox" {{ (old('_token') ? old('is_active') : $user->is_active) ? 'checked' : ''}} value="1">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Verified email')}}</label>
                <div class="form-check form-switch">
                    <input name="email_verified" class="form-check-input" id="email_verified" type="checkbox" {{ (old('_token') ? old('email_verified') : $user->email_verified) ? 'checked' : ''}} value="1">
                </div>          
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Verified phone')}}</label>
                <div class="form-check form-switch">
                    <input name="phone_verified" class="form-check-input" id="phone_verified" type="checkbox" {{ (old('_token') ? old('phone_verified') : $user->phone_verified) ? 'checked' : ''}} value="1">
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
<script src="{{ asset('admin/js/user.js') }}"></script>
<script>
    adminUser.initCreate();
</script>
@endpush