@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Edit Storage')}}</h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="page_form" method="post" action="{{ route('admin.storage.store')}}">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" value="{{ $service->id }}" />
            @include('shaun_core::admin.partial.storage.service_'.$service->key,['config' => $service->getConfig(),'extra'=> $service->getExtra()])
            <div class="form-group">
                <label class="control-label">{{__('Default')}}</label>           
                <div class="form-check form-switch">
                    <input id="is_default" class="form-check-input" name="is_default" @if ($service->is_default) onclick="return false;" @endif type="checkbox" {{old('is_default',$service->is_default) ? 'checked' : ''}} value="1">
                </div>  
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-filled-blue">
                    {{__('Save Changes')}}
                </button>
            </div>
        </form>
    </div>
</div>
@stop