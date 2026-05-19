@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Messages')}}</h5>
    </div>
    <form method="get">
        <div class="admin-card-bar">
            <select name="type" class="form-select">
                @foreach ($types as $key => $name)
                    <option @if ($key == $type) selected @endif value="{{$key}}">{{$name}}</option>
                @endforeach
            </select>
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
                <th width="45%">
                    {{__('Message')}}
                </th>
                <th>
                    {{__('Users')}}
                </th>
                <th>
                    {{__('Date')}}
                </th>
                <th width="120px">
                    {{__('Action')}}
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
                        <a href="{{route('admin.chat.detail',['id'=>$message->room_id])}}">
                            {{$message->getRoom()->getName()}}
                        </a>                        
                    </td>
                    <td>
                        {{$message->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                    </td>
                    <td>
                        <a href="{{route('admin.chat.detail',['id'=>$message->room_id])}}"> 
                            {{__('Room Detail')}}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $messages->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop