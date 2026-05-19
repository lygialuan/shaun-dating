@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <div class="admin-card-top-title">
            {{$room->getName()}}       
        </div>
    </div>
    <div class="chat-room-users-list">
        @foreach ($room->getMembers() as $member)
            <div class="chat-room-users-list-item">
                <a href="@if ($member->getUser()->getHref()) {{$member->getUser()->getHref()}} @else javascript:void(0);@endif">
                    <img src="{{$member->getUser()->getAvatar()}}" class="chat-room-users-list-item-avatar"/>
                    {{$member->getUser()->getName()}}
                </a>
            </div>
        @endforeach
    </div>
    <form method="get">
        <div class="admin-card-bar">
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input type="text" value="{{$text}}" name="text" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Message')}}
                </th>
                <th width="200">
                    {{__('Date')}}
                </th>
            </thead>
            <tbody>
                @foreach ($messages as $message)
                <tr>             
                    <td>
                        <a href="@if ($message->getUser()->getHref()) {{$message->getUser()->getHref()}} @else javascript:void(0);@endif" class="chat-sender-name">{{$message->getUser()->getTitle()}}</a>
                        <span class="chat-content">{{$message->content}}</span>
                        <div>@includeIf('shaun_chat::partial.type.'.$message->type, ['message' => $message])</div>
                    </td>
                    <td>
                        {{$message->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $messages->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop