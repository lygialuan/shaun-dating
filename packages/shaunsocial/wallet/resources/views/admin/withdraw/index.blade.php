@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Transfer Fund Requests')}}</h5>
    </div>
    <form method="get">
        <div class="admin-card-bar">
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
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input id="name" type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="export">{{__('Export data in list to excel')}}</button>
        </div>
    </form>
    <form method="post" id="withdraw_form" action="{{route('admin.wallet.withdraw.store_manage')}}">
        {{ csrf_field() }}
        <input type="hidden" id="action" name="action">
        <input type="hidden" id="status_export" name="status">
        <input type="hidden" id="name_export" name="name">
        <input type="hidden" id="type_export" name="type">
        <div class="admin-card-body table-responsive">
            <table class="admin-table table table-hover" id="withdraw_list">
                <thead>
                    <th width="30">
                        <input class="form-check-input col-check check_all" type="checkbox">
                    </th>
                    <th>
                        {{__('Date')}}
                    </th>
                    <th>
                        {{__('Name')}}
                    </th>
                    <th>
                        {{__('Payment method')}}
                    </th>
                    <th>
                        {{__('Gross')}}
                    </th>
                    <th>
                        {{__('Fee')}}
                    </th>
                    <th>
                        {{__('Net')}}
                    </th>
                    <th>
                        {{__('Status')}}
                    </th>
                    <th>
                        {{__('Action')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($withdraws as $withdraw)
                    <tr>
                        <td width="30">
                            @if ($withdraw->canAccept() || $withdraw->canReject())
                                <input class="form-check-input col-check check_item" type="checkbox" name="ids[]" value="{{$withdraw->id}}" >
                            @endif
                        </td>
                        <td>
                            {{$withdraw->created_at->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.payment'))}}
                        </td>
                        <td>
                            <?php $user = $withdraw->getUser();?>
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
                            {{$withdraw->getPaymentMethod(). ' ('.$withdraw->getExchangeInfo().')'}} - <a class="admin_modal_ajax" data-url="{{route('admin.wallet.withdraw.detail',$withdraw->id)}}" href="javascript:void(0);">{{__('view details')}}</a>
                        </td>
                        <td>
                            {{$withdraw->getGross(true)}}
                        </td>
                        <td>
                            {{$withdraw->getFee(true)}}
                        </td>
                        <td>
                            {{$withdraw->getNet(true)}}
                        </td>
                        <td>
                            {{$withdraw->getStatusText()}}
                        </td>
                        <td class="actions-cell">    
                            @if ($withdraw->canAccept())
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to accept this request?')}}" data-url="{{route('admin.wallet.withdraw.store_accept',$withdraw->id)}}">
                                    {{__('Accept')}}
                                </a>
                            @endif
                            @if ($withdraw->canReject())
                                <a href="javascript:void(0);"  class="admin_modal_confirm_delete" data-content="{{__('Do you want to reject this request?')}}" data-url="{{route('admin.wallet.withdraw.store_reject',$withdraw->id)}}">
                                    {{__('Reject')}}
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $withdraws->withQueryString()->links('shaun_core::admin.partial.paginate') }}
            <div class="form-actions">
                <button type="button" id="accept_button" class="btn-filled-blue">
                    {{__('Accept')}}
                </button>
                <button type="button" id="reject_button" class="btn-filled-red">
                    {{__('Reject')}}
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/wallet.js') }}"></script>
<script>
    adminCore.initCheckAll();
    adminWallet.initWithdrawListing();
    adminTranslate.add({
        'confirm_accept_wallet' : '{{addslashes(__('Do you want to accept these requests?'))}}',
        'confirm_reject_wallet' : '{{addslashes(__('Do you want to reject these requests?'))}}'
    });
</script>
@endpush