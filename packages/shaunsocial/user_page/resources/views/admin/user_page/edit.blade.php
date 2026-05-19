@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">
            {{__('Edit')}}  
        </h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="user_form" method="post" action="{{ route('admin.user_page.store_edit')}}">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" value="{{ $page->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Profile name')}}</label>
                <input class="form-control" name="name" value="{{old('name',$page->name)}}" type="text">
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Username')}}</label>
                <input class="form-control" name="user_name" value="{{old('user_name',$page->user_name)}}" type="text">
            </div>

            @if ($page->id != config('shaun_core.core.user_root_id')) 
                <div class="form-group">
                    <label class="control-label">{{__('Role')}}</label>            
                    <select name="role_id" class="form-select">
                        @foreach($roles as $role)
                            <option @if (old('role_id',$page->role_id) == $role->id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                        @endforeach
                    </select>             
                </div>
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" id="is_active" type="checkbox" {{ (old('_token') ? old('is_active') : $page->is_active) ? 'checked' : ''}} value="1">
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