@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Advertisings')}}</h5>
    </div>
    <form method="get">
        <div class="admin-card-bar">
            <select name="status" class="form-select">
                <option>{{__('All status')}}</option>
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
                @endforeach
            </select>
            <div>
                <input type="text" value="{{$userName}}" name="user_name" class="form-control" placeholder="{{__('Username')}}"/>
            </div>
            <div>
                <input type="text" value="{{$name}}" name="name" class="form-control" placeholder="{{__('Name')}}"/>
            </div>
            <div>
                <label class="form-label">{{__('Start date')}}</label>
                <input type="date" value="{{$start}}" name="start" class="form-control"/>
            </div>
            <div>
                <label class="form-label">{{__('End date')}}</label>
                <input type="date" value="{{$end}}" name="end" class="form-control" />
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Username')}}
                </th>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Created')}}
                </th>
                <th>
                    {{__('Date')}}
                </th>
                <th>
                    {{__('Status')}}
                </th>
                <th style="width: 15%;">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($advertisings as $advertising)
                <tr>
                    <td>
                        <?php $user = $advertising->getUser(); ?>
                        <a href="@if ($user->getHref()) {{$user->getHref()}} @else javascript:void(0);@endif" class="d-flex align-items-center gap-2 text-main-color">
                            <img src="{{$user->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                            {{$user->getName()}}
                        </a>
                    <td>
                        <?php $post = $advertising->getPost();?>
                        @if ($post)
                            <a href="{{$post->getHref()}}" target="_blank">
                                {{$advertising->name}}
                            </a>
                        @else
                            {{$advertising->name}}
                        @endif
                    </td>
                    <td>
                        {{$advertising->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                    </td>
                    <td>
                        {{$advertising->start->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.date'))}} - {{$advertising->end->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.date'))}}
                    </td>
                    <td>
                        {{$advertising->getStatusText()}}
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.advertising.detail',$advertising->id)}}">
                            {{__('Reports')}}
                        </a> 
                        @if ($advertising->canStop())
                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to stop this advertising?')}}" data-url="{{route('admin.advertising.store_stop',$advertising->id)}}">
                                {{__('Stop')}}
                            </a>   
                        @endif
                        @if ($advertising->canEnable())
                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to enable this advertising?')}}" data-url="{{route('admin.advertising.store_enable',$advertising->id)}}">
                                {{__('Enable')}}
                            </a> 
                        @endif
                        @if ($advertising->canComplete())
                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to complete this advertising?')}}" data-url="{{route('admin.advertising.store_complete',$advertising->id)}}">
                                {{__('Complete')}}
                            </a> 
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $advertisings->withQueryString()->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/advertising.js') }}"></script>
<script>
    adminAdvertising.initListing();
</script>
@endpush