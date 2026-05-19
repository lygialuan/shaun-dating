@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Transactions')}}: {{$user->getName()}}</h5>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Date')}}
                </th>
                <th>
                    {{__('Type')}}
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
                    {{__('Gateway transaction id')}}
                </th>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                <tr>
                    <td>
                        {{$transaction->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                    </td>
                    <td>
                        {{$transaction->getDescription()}}
                    </td>
                    <td>
                        {{$transaction->getGross()}}
                    </td>
                    <td>
                        {{$transaction->getFee()}}
                    </td>
                    <td>
                        {{$transaction->getNet()}}
                    </td>
                    <td>
                        {{$transaction->getTransactionId()}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $transactions->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop