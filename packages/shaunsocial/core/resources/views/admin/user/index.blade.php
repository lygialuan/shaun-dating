@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Users')}}</h5>
        <div class="admin-card-top-action">
            <a class="btn-filled-blue" href="{{route('admin.user.create')}}">{{__('Create new')}}</a>
            <a class="btn-filled-blue" href="{{route('admin.user.export_csv')}}">{{__('Export to csv')}}</a>
            <a class="btn-filled-blue admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.migrate_old_dating.create')}}">{{__('Import Users from mooDating')}}</a>
        </div>
    </div>
    <p class="admin-card-help">{{__('The members of your social network are listed here. If you need to search for a specific member, enter your search criteria in the fields below.')}}</p>
    <form method="get">
        <div class="admin-card-bar">
            <select name="status" class="form-select">
                @foreach ($statusArray as $value=> $statusName)
                    <option @if ($value == $status) selected @endif value="{{$value}}">{{$statusName}}</option>
                @endforeach
            </select>
            <select name="role_id" class="form-select">
                <option value="">{{__('All role')}}</option>
                @foreach ($roles as $value => $roleName)
                    <option @if ($value == $roleId) selected @endif value="{{$value}}">{{$roleName}}</option>
                @endforeach
            </select>
            @if (setting('user_verify.enable'))
                <select name="verify" class="form-select">
                    @foreach ($verifesArray as $value=> $verifyName)
                        <option @if ($value == $verify) selected @endif value="{{$value}}">{{$verifyName}}</option>
                    @endforeach
                </select>
            @endif
            <div>
                <input type="text" value="{{$ip}}" name="ip" class="form-control" placeholder="{{__('Ip')}}"/>
            </div>
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <form method="post" id="user_form" action="{{route('admin.user.store_manage')}}">
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
                        {{__('Email')}}
                    </th>
                    @if (setting('feature.phone_verify'))
                        <th>
                            {{__('Phone number')}}
                        </th>
                    @endif
                    <th>
                        {{__('Created')}}
                    </th>
                    <th>
                        {{__('Role')}}
                    </th>
                    <th>
                        {{__('Active')}}
                    </th>
                    <th>
                        {{__('IP')}}
                    </th>
                    <th style="width: 15%;">
                        {{__('Action')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td width="30">
                            @if (! $user->isRoot() && $user->canActionOnAdminPanel(auth()->user()))
                                <input class="form-check-input col-check check_item" type="checkbox" name="ids[]" value="{{$user->id}}" >
                            @endif
                        </td>
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
                            {{$user->email}}
                        </td>
                        @if (setting('feature.phone_verify'))
                            <td>
                                {{$user->phone_number}}
                            </td>
                        @endif
                        <td>
                            {{$user->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                        </td>
                        <td>
                            {{$user->getRole()->name}}
                        </td>
                        <td>
                            @if ($user->is_active)
                                {{__('Yes')}}
                            @else
                                {{__('No')}}
                            @endif
                        </td>
                        <td>
                            {{$user->ip}}
                        </td>
                        <td class="actions-cell">
                            @if ($user->canActionOnAdminPanel(auth()->user()))
                                <a href="{{route('admin.user.create',$user->id)}}">
                                    {{__('Edit')}}
                                </a>                            
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to log out of all of this user's devices?")}}" data-url="{{route('admin.user.remove_login_all_devices',$user->id)}}">
                                    {{__('Logout')}}
                                </a>
                                <a href="{{route('admin.user.login_as',$user->id)}}">
                                    {{__('Login as user')}}
                                </a>
                                @hasPermission('admin.user_verify.request_manage')
                                    @if (setting('user_verify.enable'))
                                        @if ($user->isVerify())                                       
                                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to unverify this user?')}}" data-url="{{route('admin.user_verify.store_unverify',$user->id)}}">
                                                {{__('Unverify')}}
                                            </a>                                                                    
                                        @else
                                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to verify this user?')}}" data-url="{{route('admin.user_verify.store_verify',$user->id)}}">
                                                {{__('Verify')}}
                                            </a> 
                                        @endif
                                    @endif
                                @endHasPermission

                                @if (setting('feature.phone_verify') && ! $user->isModerator())
                                    @if ($user->phone_verified)                                       
                                        <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to unverify phone this user?')}}" data-url="{{route('admin.user.store_phone_unverify',$user->id)}}">
                                            {{__('Unverify phone')}}
                                        </a>                                                                    
                                    @else
                                        <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to verify phone this user?')}}" data-url="{{route('admin.user.store_phone_verify',$user->id)}}">
                                            {{__('Verify phone')}}
                                        </a> 
                                    @endif
                                @endif

                                @hasPermission('admin.two_factor_provider.manage')
                                    @if ($user->getTwoFactor())
                                        <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to remove two-factor authentication for this user?')}}" data-url="{{route('admin.two_factor_provider.remove_user',$user->id)}}">
                                            {{__('Remove 2FA')}}
                                        </a>  
                                    @endif
                                @endHasPermission
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $users->withQueryString()->links('shaun_core::admin.partial.paginate') }}
            <div class="form-actions">
                <button type="button" id="delete_button" class="btn-filled-red">
                    {{__('Delete')}}
                </button>
                <button type="button" id="active_button" class="btn-filled-blue">
                    {{__('Active')}}
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/migrate_old_dating.js') }}"></script>
<script src="{{ asset('admin/js/user.js') }}"></script>
<script>
    adminCore.initCheckAll();
    adminUser.initListing();
    adminTranslate.add({
        'confirm_delete_user' : '{{addslashes(__('Are you sure you want to delete these users? All their content that they created will be deleted. It is not recommended to delete users unless they are spammers. This cannot be undone!'))}}',
        'confirm_active_user' : '{{addslashes(__('Are you sure you want to active these users?'))}}'
    });
</script>
@endpush