<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    {{--CSRF Token--}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <title>@if (isset($title)) {{$title}} - @endif {{setting('site.title')}}</title>

    {{--Styles css common--}}
    <link rel="stylesheet" href="{{asset('admin/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/main.css') }}">
    @yield('style-libraries')
    {{--Styles custom--}}
    @yield('styles')    
    @stack('scripts-header')
</head>
<body @isModerator class="logged-in" @else class="non-logged-in" @endIsModerator @if ($rtl) dir="rtl" @else dir="ltr" @endif>
    <div id="app">
        @isModerator
            @include('shaun_core::admin.partial.header')
            @include('shaun_core::admin.partial.main_menu')
            @include('shaun_core::admin.partial.message')
            @include('shaun_core::admin.partial.title_bar')
            @include('shaun_core::admin.partial.breadcrumb')
        @endIsModerator
        
        <section class="section admin-main-section">
            @yield('content')
        </section>
        @isModerator
        <div class="admin-footer-version">
            {{ __('Current Version') }}: <span class="admin-footer-version-text">{{ setting('site.version') }}</span>
        </div>
        @endIsModerator
    </div> 
    <script src="{{ asset('admin/js/lib/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/js/main.js') }}"></script>
    <script src="{{ asset('admin/js/global.js') }}"></script>
    <script src="{{ asset('admin/js/translate.js') }}"></script>
    <script src="{{asset('admin/js/lib/bootstrap.bundle.min.js')}}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        adminTranslate.add({
            'active' : '{{addslashes(__('Active'))}}',
            'inactive' : '{{addslashes(__('Inactive'))}}',
            'deactive' : '{{addslashes(__('Deactive'))}}',
            'confirm_delete_item' : '{{addslashes(__('Do you want to delete it?'))}}',
            'confirm' : '{{addslashes(__('Confirm'))}}',
            'upload_limit_error' : '{{addslashes(__('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFilesServer().'Kb']).'.')}}'
        });
        adminConfig = {
            'maxUploadSize' : {{ getMaxUploadFileSize() }}
        };
    </script>
    @stack('scripts-body')
    @include('shaun_core::admin.partial.body')
</body>
</html>