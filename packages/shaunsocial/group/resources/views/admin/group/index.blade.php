@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Group')}}</h5>
    </div>
    <form method="get">
        <div class="admin-card-bar">
            <select name="status" class="form-select">
                <option>{{__('All status')}}</option>
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
                @endforeach
            </select>
            <select name="popular" class="form-select">
                @foreach ($popularArray as $value=> $popularName)
                    <option @if ($value == $popular) selected @endif value="{{$value}}">{{$popularName}}</option>
                @endforeach
            </select>
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <form method="post" id="user_form" action="{{route('admin.group.store_manage')}}">
        {{ csrf_field() }}
        <input type="hidden" id="action" name="action">
        <div class="admin-card-body table-responsive">
            <table class="admin-table table table-hover">
                <thead>
                    <th width="30">
                        <input class="form-check-input col-check check_all" type="checkbox">
                    </th>
                    <th>
                        {{__('ID')}}
                    </th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th>
                        {{__('Status')}}
                    </th>
                    <th>
                        {{__('Popular')}}
                    </th>
                    <th>
                        {{__('Created')}}
                    </th>
                    <th style="width: 15%;">
                        {{__('Action')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($groups as $group)
                    <tr>
                        <td width="30">
                            <input class="form-check-input col-check check_item" type="checkbox" name="ids[]" value="{{$group->id}}" >
                        </td>
                        <td>
                            {{$group->id}}
                        </td>
                        <td>
                            <a target="_blank" href="{{$group->getHref()}}">
                                {{$group->name}}
                            </a>
                        </td>
                        <td>
                            {{$group->getStatusText()}}
                        </td>
                        <td>
                            @if ($group->is_popular)
                                {{__('Yes')}}
                            @else
                                {{__('No')}}
                            @endif
                        </td>
                        <td>
                            {{$group->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                        </td>
                        <td class="actions-cell">
                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to delete this group?")}}" data-url="{{route('admin.group.delete',$group->id)}}">
                                {{__('Delete')}}
                            </a> 
                            @if ($group->canDisable())
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to disable this group?")}}" data-url="{{route('admin.group.disable',$group->id)}}">
                                    {{__('Disable')}}
                                </a> 
                            @endif
                            @if ($group->canApprove())
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to approve this group?")}}" data-url="{{route('admin.group.approve',$group->id)}}">
                                    {{__('Approve')}}
                                </a> 
                            @endif
                            @if ($group->canActive())
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to active this group?")}}" data-url="{{route('admin.group.active',$group->id)}}">
                                    {{__('Active')}}
                                </a> 
                            @endif
                            @if (! $group->is_popular)
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to promote this group by making it as popular manually? It will appear in the 'popular groups' block along with other popular groups")}}" data-url="{{route('admin.group.popular',$group->id)}}">
                                    {{__('Popular')}}
                                </a> 
                            @else
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to remove this group from 'Popular groups' block to stop promoting it?")}}" data-url="{{route('admin.group.remove_popular',$group->id)}}">
                                    {{__('Remove Popular')}}
                                </a> 
                            @endif
                            <a href="{{route('admin.group.admin_manage',$group->id)}}">
                                {{__('Admin manage')}}
                            </a>    
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $groups->withQueryString()->links('shaun_core::admin.partial.paginate') }}
            <div class="form-actions">
                <button type="button" id="delete_button" class="btn-filled-red">
                    {{__('Delete')}}
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/group.js') }}"></script>
<script>
    adminCore.initCheckAll();
    adminGroup.initListing();
    adminTranslate.add({
        'confirm_delete_group' : '{{addslashes(__('Are you sure you want to delete these group?'))}}',
    });
</script>
@endpush