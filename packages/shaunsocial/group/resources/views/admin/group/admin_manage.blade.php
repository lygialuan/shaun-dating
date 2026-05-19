@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">
            {{$title}}  
        </h5>
    </div>
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
                    {{__('Role')}}
                </th>
                <th style="width: 15%;">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                <?php
                    $user = $admin->getUser(); 
                    if (! $user) {
                        continue;
                    }
                ?>
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
                        {{$admin->getRoleName()}}
                    </td>
                    <td class="actions-cell">
                        @hasPermission('admin.user.manage')
                            <a href="{{route('admin.user.login_as',$user->id)}}">
                                {{__('Login as user')}}
                            </a>
                        @endHasPermission
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop