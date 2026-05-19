@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Users')}}</h5>
    </div>    
    <form method="get">
        <div class="admin-card-bar">
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
                    {{__('Email')}}
                </th>
                <th>
                    {{__('Current balance')}}
                </th>
                <th style="width: 15%;">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>
                        <a target="_blank" href="{{$user->getHref()}}" class="d-flex align-items-center gap-2 text-main-color">
                            <img src="{{$user->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                            {{$user->name}}
                        </a>
                    </td>
                    <td>
                        {{$user->email}}
                    </td>
                    <td>
                        {{formatNumber($user->getCurrentBalance())}}
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.wallet.transactions',$user->id)}}">{{__('Transactions')}}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $users->withQueryString()->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop