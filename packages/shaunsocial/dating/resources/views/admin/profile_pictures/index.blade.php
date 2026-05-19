@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{$title}}</h5>
    </div>
    <form method="post" id="user_form">
        {{ csrf_field() }}
        <input type="hidden" id="action" name="action">
        <div class="admin-card-body table-responsive">
            <table class="admin-table table table-hover">
                <thead>
                    <th>
                        {{__('ID')}}
                    </th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th>
                        {{__('New profile pictures')}}
                    </th>
                    <th>
                        {{__('Created date')}}
                    </th>
                    <th style="width: 15%;">
                        {{__('Action')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>
                            {{$user->id}}
                        </td>
                        <td>
                            <a target="_blank" href="{{$user->getHref()}}" class="d-flex align-items-center gap-2 text-main-color">
                                <img src="{{$user->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                                {{$user->name}}
                            </a>
                        </td>
                        <td>
                            @php
                                $photos = $user->getPhotoVerifyItem('pending');
                            @endphp
                            @if($photos && $photos->isNotEmpty())
                                @foreach($photos as $photo)
                                <a href="{{ $photo->getFile()?->getUrl() }}" target="_blank">
                                    <img src="{{ $photo->getFile()?->getUrl() }}" width="50">
                                </a>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            {{$user->photos_uploaded_at?->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                        </td>
                        <td class="actions-cell">
                            @if($photos && $photos->isNotEmpty())
                                <a href="{{route('admin.dating.profile_pictures.view_detail',$user->id)}}">
                                    {{__('View detail')}}
                                </a>   
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $users->withQueryString()->links('shaun_core::admin.partial.paginate') }}
        </div>
    </form>
</div>
@stop