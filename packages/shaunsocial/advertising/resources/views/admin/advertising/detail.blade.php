@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Advertising')}}: {{$advertising->name}}</h5>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Date')}}
                </th>
                <th>
                    {{__('Views')}}
                </th>
                <th>
                    {{__('Clicks')}}
                </th>
                <th>
                    {{__('Total spent')}}
                </th>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                <tr>
                    <td>
                        {{$report->date->setTimezone(auth()->user()->timezone)->isoFormat(config('shaun_core.time_format.date'))}}
                    </td>
                    <td>
                        {{formatNumberNoRound($report->view_count)}}
                    </td>
                    <td>
                        {{formatNumberNoRound($report->click_count)}}
                    </td>
                    <td>
                        {{formatNumber($report->total_amount)}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
        {{ $reports->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop