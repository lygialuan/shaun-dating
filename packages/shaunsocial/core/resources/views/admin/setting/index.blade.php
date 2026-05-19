@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-grid-block-cont">
    <form action="{{ route('admin.setting.store') }}" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="group_id" value="{{$group->id}}" />
        {{ method_field("POST") }}
        {{ csrf_field() }}
        <div class="form-body form-body-group">
            @foreach ($group->groupSubs as $key => $groupSub)
                @include('shaun_core::admin.partial.setting.group_sub',['groupSub' => $groupSub])
            @endforeach
        </div>
    </form>
</div>
@stop

@push('scripts-body')
    <script src="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.css')}}">
    <link rel="stylesheet" href="{{ asset('admin/js/lib/jquery-minicolors/jquery.minicolors.css') }}">
    <script src="{{ asset('admin/js/lib/jquery-minicolors/jquery.minicolors.min.js') }}"></script>
    <script src="{{ asset('admin/js/setting.js') }}"></script>
    <script>
        $('document').ready(function () {
            adminSetting.init('{{route('admin.setting.store')}}');            
        });
    </script>
@endpush
