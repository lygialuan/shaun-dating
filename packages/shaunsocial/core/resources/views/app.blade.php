<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        @php
            $titlePage = '';
            $titlePage .= setting('site.title');
            if (isset($title)) {
                $titlePage .=  ' | ' .$title;
            }

            $descriptionPage = setting('site.description');
            if (! empty($description)) {
                $descriptionPage = $description;
            }
        @endphp

        <title>{!! makeTextForHeader($titlePage, false) !!}</title>
        <meta name="description" content="{!! makeTextForHeader($descriptionPage) !!}" />
        <meta name="keywords" content="@if (! empty($keywords)) {!! makeTextForHeader($keywords) !!} @else {!! makeTextForHeader(setting('site.keywords')) !!} @endif"/>

        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <meta property="og:site_name" content="{!! makeTextForHeader(setting('site.title')) !!}" />
        <meta property="og:title" content="{!! makeTextForHeader($titlePage) !!}" />
        <meta property="og:description" content="{!! makeTextForHeader($descriptionPage) !!}" />
        <meta property="og:url" content="{{url()->full()}}" />
        <meta property="og:image" content="@if (isset($ogImage)){{$ogImage}}@else{{setting('site.og_image')}}@endif" />

        @if (checkEnablePWA()) 
            <link rel="manifest" href="{{ asset('manifest.json') }}">
        @endif

        <!-- Styles -->
        @vite('resources/css/main.css')
        @if($theme)
            <link rel="stylesheet" href="{{ $theme->getAssetCss() }}?{{setting('site.cache_number')}}" />
        @endif
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}" />

        <!-- Script -->
        <script>
            window.siteConfig = {
                siteUrl: '{{setting('site.url')}}',
                siteLogo: '{{setting('site.logo')}}',
                siteLogoDarkMode: '{{setting('site.logo_darkmode')}}',
                headerMobileLogo: '{{setting('site.header_mobile_logo_mobile')}}',
                headerMobileLogoDarkMode: '{{setting('site.header_mobile_logo_mobile_darkmode')}}',
                languageDefault : '{{Config::get('app.locale')}}',
                languages : {{ Js::from($languages) }},
                siteName: '{!! makeTextForHeader(setting('site.title')) !!}',
                mustLogin: @if (isset($mustLogin)){{ $mustLogin}} @else false @endif,
                cdn: '@if (config('disks.public.url')){{config('disks.public.url')}}@else{{setting('site.url')}}@endif',                
                profilePrefix: '{{config('shaun_core.core.prefix_profile')}}',
                offline: @if (setting('site.offline')) true @else false @endif,
                recaptchaType: '{{setting('spam.capcha_type')}}',
                recapchaPublicKey : '{{setting('spam.recapcha_public_key')}}',
                turnstileSiteKey : '{{setting('spam.turnstile_site_key')}}',
                cacheNumber: '{{setting('site.cache_number')}}',
                rtl: @if ($rtl) true @else false @endif,
                forceLogin: @if (setting('feature.force_login')) true @else false @endif,
                dataExtra: '@if (isset($dataExtra)){{json_encode($dataExtra)}}@endif',
            }
        </script>

        {!! setting('site.head_script') !!}
    </head>
    <body class="bg-body text-main-color dark:bg-dark-body dark:text-white">
        <div id="app"></div>
        @vite('resources/js/app.js')
        {!! setting('site.body_script') !!}
    </body>
</html>
