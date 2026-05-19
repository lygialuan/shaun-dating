@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Verification Requests')}}</h5>
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
    <form method="post" id="user_form" action="{{route('admin.user_verify.store_manage')}}">
        {{ csrf_field() }}
        <input type="hidden" id="action" name="action">
        <div class="admin-card-body table-responsive">
            <table class="admin-table table table-hover">
                <thead>
                    <th width="30">
                        <input class="form-check-input col-check check_all" type="checkbox">
                    </th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th>
                        {{__('Email')}}
                    </th>
                    <th>
                        {{__('Documents')}}
                    </th>
                    <th>
                        {{__('Date')}}
                    </th>
                    <th>
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
                            <a target="_blank" href="{{$user->getHref()}}" class="d-flex align-items-center gap-2 text-main-color">
                                <img src="{{$user->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                                {{$user->name}}
                            </a>
                        </td>
                        <td>
                            {{$user->email}}
                        </td>
                        <td>
                            @foreach ($user->getVerifyFiles() as $key => $verifyFile)
                                <div>
                                    <a target="_blank" href="{{$verifyFile->getFile('file_id')->getUrl()}}">{{__('Document')}} {{$key + 1}}</a>
                                </div>
                            @endforeach
                        </td>
                        <td>
                            {{$user->verify_status_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                        </td>
                        <td class="actions-cell">                            
                            <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to verify this request?')}}" data-url="{{route('admin.user_verify.store_verify',$user->id)}}">
                                {{__('Verify')}}
                            </a>
                            <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.user_verify.reject',$user->id)}}">
                                {{__('Reject')}}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $users->withQueryString()->links('shaun_core::admin.partial.paginate') }}
            <div class="form-actions">
                <button type="button" id="reject_button" class="btn-filled-red">
                    {{__('Reject')}}
                </button>
                <button type="button" id="verify_button" class="btn-filled-blue">
                    {{__('Verify')}}
                </button>
            </div>
        </div>
    </form>
</div>
@stop


@push('scripts-body')
<script src="{{ asset('admin/js/verify.js') }}"></script>
<script>
    adminCore.initCheckAll();
    adminVerify.initListing();
    adminTranslate.add({
        'confirm_verify_user' : '{{addslashes(__('Are you sure you want to verify these requests?'))}}',
        'confirm_reject_user' : '{{addslashes(__('Are you sure you want to reject these requests?'))}}'
    });
</script>
@endpush