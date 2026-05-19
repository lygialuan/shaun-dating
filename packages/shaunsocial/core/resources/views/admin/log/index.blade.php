@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('System Log')}}</h5>
    </div>
    <p class="admin-card-help">{{__('System logs are helpful for troubleshooting and debugging. Select the log you would like to view below.')}}</p>
    <form method="POST" method="post" action="{{ route('admin.log.index')}}">
        <div class="admin-card-bar">
            {{ csrf_field() }}
            <select id="file" name="file" class="form-select">
                <option value=""></option>
                @foreach ($files as $file)
                    <option @if ($fileName == $file) selected @endif value="{{$file}}">{{$file}}</option>
                @endforeach
            </select>
            <select name="line" class="form-select">
                @foreach ($lines as $lineNumber)
                    <option @if ($lineNumber == $line) selected @endif value="{{$lineNumber}}">{{__('Show :line lines',['line' => $lineNumber])}}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-filled-blue">{{__('View Log')}}</button>
            <button type="button" id="download_log" class="btn-filled-blue">{{__('Download Log')}}</button>
        </div>
    </form>
    <div class="card-content">
        @if ($log)
            <div class="admin-logs">
                <pre>
                    {{$log}}
                </pre>
            </div>
        @endif
    </div>
</div>
@stop

@push('scripts-body')
<script>
    $('#download_log').click(function(){
        if ($('#file').val() != ''){
            window.location.href = "{{route('admin.log.download')}}/" + $('#file').val();
        }
    });
</script>
@endpush