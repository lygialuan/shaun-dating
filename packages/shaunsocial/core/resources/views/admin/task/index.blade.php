@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Tasks')}}</h5>
    </div>
    <p class="admin-card-help">{{__("These are actions that are run silently in the background.")}}</p>
    <div class="admin-message message-notice admin-message-task p-3 mb-3" role="alert">
        <div class="mb-1">
            {{__('Please set the following commands to run in crontab about every 1 minute')}}:
        </div>
        <ul class="mb-1">
            <li>{{__('Requires command line utility (VPS)')}}: "cd {{base_path()}} && php artisan schedule:run >> /dev/null 2>&1"</li>
            <li>{{__('Requires command line utility (cPanel)')}}: "/usr/local/bin/php {{base_path()}}/artisan schedule:run >/dev/null 2>&1"</li>
        </ul>
        <div>{{__('Succeeded')}}: @if($time){{__('last on').' '.$time}}@else{{__('No, cron job configuration is not correct.')}}@endif</div>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Command')}}
                </th>
                <th>
                    {{__('Expression')}}
                </th>
                <th>
                    {{__('Description')}}
                </th>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                <tr>
                    <td>
                        {{$task['command']}}
                    </td>
                    <td>
                        {{$task['expression']}}
                    </td>
                    <td>
                        {{$task['description']}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop