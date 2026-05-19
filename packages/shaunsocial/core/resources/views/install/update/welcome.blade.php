@extends('shaun_core::install.layouts.master-update')

@section('container')
    <div>
        <div>
            <strong>{{__('*Make sure that you backup all files and databases before proceeding, please.')}}</strong>
        </div>
        <div>
            <strong>{{__('*Do not close your browser during the upgrading process, please.')}}</strong>
        </div>
    </div>
    <div class="text-center mt-10">
        <a class="button blue" href="{{ route('update.overview') }}">
            {{ __('Next Step') }}
        </a>
    </div>
@stop
