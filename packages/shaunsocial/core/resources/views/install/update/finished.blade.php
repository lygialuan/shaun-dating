@extends('shaun_core::install.layouts.master-update')

@section('template_title')
{{ __('Upgrade Finished') }}
@endsection

@section('container')
<div>
    <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-3 rounded relative mb-5 max-w-lg mx-auto text-center">
        <strong>
            {{__('Application has been successfully upgraded.')}}
        </strong>
    </div>
</div>
<div class="text-center">
    <a href="{{ url('/') }}" class="button blue">{{ __('Go to Home Page') }}</a>
</div>

@endsection