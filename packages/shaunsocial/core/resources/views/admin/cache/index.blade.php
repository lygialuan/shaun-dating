@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Cache')}}</h5>
    </div>
    <div class="admin-card-body">
        <form id="cache_form" method="post" action="{{ route('admin.cache.store')}}">
            <div id="errors">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Caching Type')}}</label>
                <select id="driver" name="driver" class="form-select">
                    @foreach($drivers as $key=>$name)
                        <option @if ($key == $cacheSetting['driver']) selected @endif value="{{$key}}">{{$name}}</option>
                    @endforeach
                </select>
            </div>
            @foreach($drivers as $key=>$name)
                <div id="content_{{$key}}" class="cache-driver" style="@if ($key != $cacheSetting['driver']) display: none @endif">
                    @includeIf('shaun_core::admin.partial.cache.'.$key, ['cacheSetting' => $cacheSetting])
                </div>
            @endforeach
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
<script src="{{ asset('admin/js/cache.js') }}"></script>
<script>
    adminCache.initSave('{{route('admin.cache.store')}}');
</script>
@endpush