@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Edit Translate Provider')}}</h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="page_form" method="post" action="{{ route('admin.translate_provider.store')}}">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" value="{{ $provider->id }}" />
            @include('shaun_core::admin.partial.translate_provider.provider_'.$provider->type,['config' => $provider->getConfig()])
            <div class="form-group">
                <label class="control-label">{{__('Default')}}</label>           
                <div class="form-check form-switch">
                    <input id="is_default" class="form-check-input" name="is_default" @if ($provider->is_default) onclick="return false;" @endif type="checkbox" {{old('is_default',$provider->is_default) ? 'checked' : ''}} value="1">
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