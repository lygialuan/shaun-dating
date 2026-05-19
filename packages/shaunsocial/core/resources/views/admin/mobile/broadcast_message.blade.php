@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Create New Broadcast Message')}}</h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="broadcast_message_form" method="post" action="{{ route('admin.mobile.store_broadcast_message')}}">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{__('Message')}}</label>
                <div class="control">
                    <input class="form-control" name="message" value="{{old('message')}}" type="text">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Link')}}</label>
                <div class="control">
                    <input class="form-control" name="link" value="{{old('link')}}" type="text">
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