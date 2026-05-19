@extends('shaun_core::install.layouts.master-update')

@section('container')
    <p class="paragraph text-center">{{ __('Current version :current | Upgrade version available :new', ['current' => $currentVersion, 'new' => $newVersion]) }}</p>
    <div class="text-center mt-10">
        <a class="button blue" href="{{ route('update.database') }}">
            {{ __('Install Updates') }}
        </a>
    </div>
@stop
