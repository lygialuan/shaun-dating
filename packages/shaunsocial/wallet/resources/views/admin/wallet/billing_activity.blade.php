@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__("System's billing activities")}}</h5>
    </div>
    <p class="admin-card-help">{{__('This section list all transactions occurring between the system and members thru eWallet.')}}</p>
    <form method="get">
        <div class="admin-card-bar">
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input id="name" type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
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
            <div class="wallet_custom">
                <label>{{__('From date')}}</label>
                <input type="date" value="{{$fromDate}}" name="from_date" class="form-control"/>
            </div>
            <div class="wallet_custom">
                <label>{{__('To date')}}</label>
                <input type="date" value="{{$toDate}}" name="to_date" class="form-control" />
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    
    @if (! $type || $type == 'deposit')
        <div class="admin-card-help">
            {{__('Total deposits (total amount of money that people or organizations put into platform)')}}: {{formatNumber($totalDeposit)}} {{getWalletTokenName()}} 
        </div>
    @endif

    @if (! $type || $type == 'payment')
        <div class="admin-card-help">
            {{__('Total payments received (all payments the platform received from members thru eWallet)')}}: {{formatNumber($totalPayment)}} {{getWalletTokenName()}} 
        </div>
    @endif

    @if (! $type || $type == 'withdraw')
        <div class="admin-card-help">
            {{__('Total withdrawal amount (included withdrawal fee)')}}: {{formatNumber($totalWithdraw)}} {{getWalletTokenName()}} 
        </div>
    @endif
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('User')}}
                </th>
                <th>
                    {{__('Description')}}
                </th>
                <th>
                    {{__('Date')}}
                </th>
                <th>
                    {{__('Amount')}}
                </th>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                <tr>
                    <td>
                        @if ($transaction->from_user_id)
                            <?php $user = $transaction->getFromUser();?>
                            @if ($user)
                                <a target="_blank" href="{{$user->getHref()}}" class="d-flex align-items-center gap-2 text-main-color">
                                    <img src="{{$user->getAvatar()}}" class="rounded-full" width="32" height="32" style="object-fit: cover; object-position: center;"/>
                                    {{$user->name}}
                                </a>
                            @else
                                {{__('Deleted user')}}
                            @endif
                        @endif
                    </td>
                    <td>
                        {{$transaction->getDescription()}}
                    </td>
                    <td>
                        {{$transaction->created_at->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.payment'))}}
                    </td>
                    <td>
                        {{$transaction->getNet()}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $transactions->withQueryString()->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/wallet.js') }}"></script>
<script>
    adminWallet.initBillingActivityListing()
</script>
@endpush