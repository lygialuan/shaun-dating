@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Subscriptions')}}</h5>
    </div>
    <form method="get">
        <div class="admin-card-bar">
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input id="name" type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <select name="status" id="status" class="form-select">
                <option value="">{{__('All')}}</option>
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
                @endforeach
            </select>
            <select name="type" id="type" class="form-select">
                <option value="">{{__('All')}}</option>
                @foreach ($typeArray as $value=> $typeName)
                    <option @if ($value == $type) selected @endif value="{{$value}}">{{$typeName}}</option>
                @endforeach
            </select>
            <select name="date_type" id="date_type" class="form-select">
                @foreach ($dateTypeArray as $value=> $dateTypeName)
                    <option @if ($value == $dateType) selected @endif value="{{$value}}">{{$dateTypeName}}</option>
                @endforeach
            </select>
            <div class="subscription_custom">
                <label>{{__('From date')}}</label>
                <input type="date" value="{{$fromDate}}" name="from_date" class="form-control"/>
            </div>
            <div class="subscription_custom">
                <label>{{__('To date')}}</label>
                <input type="date" value="{{$toDate}}" name="to_date" class="form-control" />
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>

    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('User')}}
                </th>
                <th>
                    {{__('Status')}}
                </th>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Created date')}}
                </th>
                <th style="width: 15%;">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($subscriptions as $subscription)
                <tr>
                    <td>
                        <?php $user = $subscription->getUser();?>
                        @if ($user)
                            <a target="_blank" href="{{$user->getHref()}}" class="d-flex align-items-center gap-2 text-main-color">
                                <img src="{{$user->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                                {{$user->name}}
                            </a>
                        @else
                            {{__('Deleted user')}}
                        @endif
                    </td>
                    <td>
                        {{$subscription->getStatusText()}}
                    </td>
                    <td>
                        {{$subscription->getName()}}
                    </td>
                    <td>
                        {{$subscription->created_at->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.payment'))}}
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.subscription.detail',$subscription->id)}}">{{__('View')}}</a>
                        @if ($subscription->canCancel())
                            <a class="admin_modal_confirm_delete" data-content="{{__('Do you want cancel this subscription?')}}" data-url="{{route('admin.subscription.cancel',$subscription->id)}}" href="javascript:void(0);" href="">{{__('Cancel')}}</a>
                        @endif
                        @if ($subscription->canResumeOnAdmin())
                            <a class="admin_modal_confirm_delete" data-content="{{__('Do you want cancel this resume?')}}" href="javascript:void(0);" data-url="{{route('admin.subscription.resume',$subscription->id)}}" href="">{{__('Resume')}}</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $subscriptions->withQueryString()->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/subscription.js') }}"></script>
<script>
    adminSubscription.initListing()
</script>
@endpush