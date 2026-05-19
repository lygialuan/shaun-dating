@extends('shaun_core::install.layouts.master')

@section('template_title')
    {{ __('Welcome') }}
@endsection

@section('title')
    {{ __('Installer') }}
@endsection

@section('container')
    <p class="text-center my-6">
      {{ __('Easy Installation and Setup Wizard.') }}
    </p>
    <p class="text-center">
      <a href="{{ route('install.requirements') }}" class="button blue">
        {{ __('Check Requirements') }}
      </a>
    </p>
@endsection
