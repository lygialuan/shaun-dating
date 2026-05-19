@extends('shaun_core::install.layouts.master-update')

@section('template_title')
    {{ __('Error!') }}
@endsection

@section('title')
    <i class="fa fa-flag-checkered fa-fw" aria-hidden="true"></i>
    {{ __('An error has occurred!') }}
@endsection
@section('title', __('An error has occurred!'))
@section('container')
    <p class="paragraph text-center has-error">{{ $response['message'] }}</p>
    <div class="text-center">
        <a href="{{ url('/') }}" class="button blue">{{ __('Click here to exit') }}</a>
    </div>
@stop
