@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Subscription')}}: {{$subscription->getName()}}</h5>
        @if ($subscription->canCancel())
            <a class="admin_modal_confirm_delete btn btn-filled-red" data-content="{{__('Do you want cancel this subscription?')}}" data-url="{{route('admin.subscription.cancel',$subscription->id)}}" href="javascript:void(0);" href="">{{__('Cancel')}}</a>
        @endif
        @if ($subscription->canResumeOnAdmin())
            <a class="admin_modal_confirm_delete btn btn-filled-blue" data-content="{{__('Do you want cancel this resume?')}}" href="javascript:void(0);" data-url="{{route('admin.subscription.resume',$subscription->id)}}" href="">{{__('Resume')}}</a>
        @endif
    </div>
    <div>
        <div class="mb-1">
            {{__('User')}}: 
            <?php $user = $subscription->getUser();?>
            @if ($user)
                <a target="_blank" href="{{$user->getHref()}}">
                    {{$user->name}}
                </a>
            @else
                {{__('Deleted user')}}
            @endif
        </div>
        <?php 
            $nextPayment = '';
            if ($subscription->expired_at) {
                $nextPayment = $subscription->expired_at->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.payment'));
            }
        ?>
        <div class="mb-1">{{__('Created date')}}: {{$subscription->created_at->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.payment'))}}</div>
        @if ($subscription->status->value == 'active')
            @if ($subscription->trial_day)
                <div class="mb-1">{{__('Trial')}}: {{$subscription->trial_day}} {{__('day(s)')}}</div>
            @endif
            <div class="mb-1">{{__('Subscription renewal date')}}: {{$nextPayment}}</div>
            <div class="mb-1">{{__('You will be charged')}}: {{$subscription->getPrice()}}</div>
            <div class="mb-1">{{__('Payment method')}}: {{$subscription->getGateway()->name}}</div>
        @endif
            
        @if ($subscription->status->value == 'cancel')
            <div class="mb-1">{{__('Subscription will stop at')}}: {{$nextPayment}}</div>
        @endif

        <div class="mb-1">{{__('Status')}}: {{$subscription->getStatusText()}}</div>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Date')}}
                </th>
                <th>
                    {{__('Status')}}
                </th>
                <th>
                    {{__('Transaction ID')}}
                </th>
                <th>
                    {{__('Amount')}}
                </th>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                <tr>
                    <td>
                        {{$transaction->created_at->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.payment'))}}
                    </td>
                    <td>
                        {{$transaction->getStatusText()}}
                    </td>
                    <td>
                        {{$transaction->getTransactionId()}}
                    </td>
                    <td>
                        {{$transaction->getPrice()}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $transactions->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop