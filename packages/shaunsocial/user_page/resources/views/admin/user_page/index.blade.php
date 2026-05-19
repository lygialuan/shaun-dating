@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Manage Profiles')}}</h5>
    </div>
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
    <form method="post" id="user_form" action="{{route('admin.user_page.store_manage')}}">
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
                        {{__('Created')}}
                    </th>
                    <th>
                        {{__('Role')}}
                    </th>
                    @if (setting('ai_chat_profiles.enable'))
                    <th>
                        {{__('AI Chat Enable')}}
                    </th>
                    @endif                   
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
                    @foreach ($pages as $page)
                    <tr>
                        <td width="30">
                            @if (! $page->isRoot() && $page->canActionOnAdminPanel(auth()->user()))
                                <input class="form-check-input col-check check_item" type="checkbox" name="ids[]" value="{{$page->id}}" >
                            @endif
                        </td>
                        <td>
                            {{$page->id}}
                        </td>
                        <td>
                            <a target="_blank" href="{{$page->getHref()}}" class="d-flex align-items-center gap-2 text-main-color">
                                <img src="{{$page->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                                {{$page->name}}
                            </a>
                        </td>
                        <td>
                            {{$page->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                        </td>
                        <td>
                            {{$page->getRole()->name}}
                        </td>
                        @if (setting('ai_chat_profiles.enable'))
                            <td>
                                @if ($page->getAIPersonaConfig()?->enabled)
                                    {{__('Yes')}}
                                @else
                                    {{__('No')}}
                                @endif
                            </td>
                        @endif                   
                        <td>
                            @if ($page->is_active)
                                {{__('Yes')}}
                            @else
                                {{__('No')}}
                            @endif
                        </td>
                        <td>
                            {{$page->ip}}
                        </td>
                        <td class="actions-cell">
                            @if ($page->canActionOnAdminPanel(auth()->user()))
                                <a href="{{route('admin.user_page.edit',$page->id)}}">
                                    {{__('Edit')}}
                                </a>  
                                <a href="{{route('admin.user_page.admin_manage',$page->id)}}">
                                    {{__('Admin manage')}}
                                </a>        
                                @if (setting('ai_chat_profiles.enable'))
                                <a href="{{route('admin.user_page.ai_config.edit',$page->id)}}">
                                    {{__('AI Config')}}
                                </a>
                                @endif                   
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__("Do you want to log out of all of this page's devices?")}}" data-url="{{route('admin.user_page.remove_login_all_devices',$page->id)}}">
                                    {{__('Logout')}}
                                </a>
                                @hasPermission('admin.user_page.manage_verifes')
                                    @if (setting('user_verify.enable'))
                                        @if ($page->isVerify())                                       
                                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to unverify this page?')}}" data-url="{{route('admin.user_page.verify.store_unverify',$page->id)}}">
                                                {{__('Unverify')}}
                                            </a>                                                                    
                                        @else
                                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to verify this page?')}}" data-url="{{route('admin.user_page.verify.store_verify',$page->id)}}">
                                                {{__('Verify')}}
                                            </a> 
                                        @endif
                                    @endif
                                @endHasPermission
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $pages->withQueryString()->links('shaun_core::admin.partial.paginate') }}
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
<script src="{{ asset('admin/js/user_page.js') }}"></script>
<script>
    adminCore.initCheckAll();
    adminUserPage.initListing();
    adminTranslate.add({
        'confirm_delete_user' : '{{addslashes(__('Are you sure you want to delete these pages? All their content that they created will be deleted. It is not recommended to delete users unless they are spammers. This cannot be undone!'))}}',
        'confirm_active_user' : '{{addslashes(__('Are you sure you want to active these pages?'))}}'
    });
</script>
@endpush