@extends('shaun_core::install.layouts.master')

@section('template_title')
    {{ __('Installation Finished') }}
@endsection

@section('title')
    <i class="fa fa-flag-checkered fa-fw" aria-hidden="true"></i>
    {{ __('Installation Finished') }}
@endsection

@section('container')

    <div class="text-center">
        <a href="{{ url('/') }}" class="button blue">{{ __('Go to Home Page') }}</a>
    </div>

@endsection
