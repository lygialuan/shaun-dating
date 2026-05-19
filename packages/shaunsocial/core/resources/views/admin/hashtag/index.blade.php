@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Hashtags')}}</h5>
        <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.hashtag.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>
    
    <form method="get">
        <div class="admin-card-bar">
            <select name="status" class="form-select">
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
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
                <th width="150">
                    {{__('Status')}}
                </th>
                <th width="150">
                    {{__('Active')}}
                </th>
            </thead>
            <tbody>
                @foreach ($hashtags as $hashtag)
                <tr>
                    <td>
                        {{$hashtag->name}}
                    </td>
                    <td width="150">
                        <span id="status-{{$hashtag->id}}">
                            {{$hashtag->is_active ? __('Active') : __('Inactive')}}
                        </span>
                    </td>                          
                    <td class="actions-cell" width="150">
                        <label class="button-switch">
                            <input class="is_active" data-id="{{$hashtag->id}}" type="checkbox" {{ $hashtag->is_active ? 'checked' : ''}} value="1">
                            <span class="status-action" id="status-action-{{$hashtag->id}}">{{$hashtag->is_active ? __('Deactive') : __('Active')}}</span>
                        </label>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $hashtags->withQueryString()->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/hashtag.js') }}"></script>
<script>
    adminHashtag.initListing('{{route('admin.hashtag.store_active')}}', '{{route('admin.hashtag.index')}}');
</script>
@endpush