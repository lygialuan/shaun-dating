@extends('shaun_core::admin.layouts.master')

@section('content')
<form action="{{ route('admin.theme.store_setting') }}" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="{{$id}}"/>
    <input type="hidden" name="type" value="{{$type}}"/>
    <div class="admin-card-bar">
        <button type="submit" id="save_change" class="btn-filled-blue">
            {{'Save changes'}}
        </button>
        <button type="button" class="btn-filled-blue admin_modal_confirm_delete" data-content="{{__('Do you want to reset this theme?')}}" data-url="{{route('admin.theme.store_reset_setting',['type' => $type, 'id' => $id])}}">
            {{'Reset'}}
        </button>
    </div>
    <div class="admin-grid-block-cont">
        {{ method_field("POST") }}
        {{ csrf_field() }}
        <div class="form-body form-body-group">
            @foreach ($settings as $key => $setting)
                <div class="admin-card has-form-group">
                    <div class="admin-card-top">
                        <h5 class="admin-card-top-title">{{$setting['title']}}</h5>
                        @isset($setting['guide_image'])
                            <a href="{{$setting['guide_image']}}" target="_blank" class="admin-card-top-link-preview">
                                <span class="material-symbols-outlined notranslate">preview</span>
                            </a>
                        @endisset
                    </div>
                    <div class="admin-card-body">
                        @foreach ($setting['childs'] as $child)
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center gap-5">
                                <label class="control-label-text">{{$child['title']}}</label>
                                <div class="control-field">
                                    <input type="text" class="input form-control mini-color" name="{{$child['name']}}" value="{{$child['value']}}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</form>
@stop

@push('scripts-body')
<link rel="stylesheet" href="{{ asset('admin/js/lib/jquery-minicolors/jquery.minicolors.css') }}">
<script src="{{ asset('admin/js/lib/jquery-minicolors/jquery.minicolors.min.js') }}"></script>
<script>
$('.mini-color').minicolors({
    swatches: ['#ef9a9a','#90caf9','#a5d6a7','#fff59d','#ffcc80','#bcaaa4','#eeeeee','#f44336','#2196f3','#4caf50','#ffeb3b','#ff9800','#795548','transparent'],
    keywords: 'transparent, initial, inherit'
});
</script>
@endpush