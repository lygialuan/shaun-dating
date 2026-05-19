@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Story Songs')}}</h5>
        <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.story.song.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>
    <form method="get">
        <div class="admin-card-bar">
            <select name="status" class="form-select">
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
                @endforeach
            </select>
            <select name="default" class="form-select">
                @foreach ($defaultArray as $value=> $defaultName)
                    <option @if ($value == $default) selected @endif value="{{$value}}">{{$defaultName}}</option>
                @endforeach
            </select>
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                </th>
                <th width="150">
                    {{__('Status')}}
                </th>
                <th width="150">
                    {{__('Default')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($songs as $song)
                <tr>
                    <td>
                        {{$song->name}} 
                    </td>
                    <td>
                        <audio controls>
                            <source src="{{$song->getUrl()}}" type="audio/mpeg">
                        </audio>
                    </td>
                    <td width="150">
                        {{$song->is_active ? __('Active') : __('Inactive')}}
                    </td>  
                    <td width="150">
                        {{$song->is_default ? __('Yes') : __('No')}}
                    </td>                          
                    <td class="actions-cell" width="150">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.story.song.create',$song->id)}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.story.song.delete',$song->id)}}">
                            {{__('Delete')}}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $songs->withQueryString()->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/story.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminStory.initSongListing();
</script>
@endpush