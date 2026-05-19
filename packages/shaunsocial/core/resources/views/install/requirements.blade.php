@extends('shaun_core::install.layouts.master')

@section('template_title')
    {{ __('Step 1 | Server Requirements') }}
@endsection

@section('title')
    <i class="fa fa-list-ul fa-fw" aria-hidden="true"></i>
    {{ __('Server Requirements') }}
@endsection

@section('container')
    <ul class="list-none m-0 p-0 mb-4 border">
        <li class="bg-base-blue text-white p-3">
            <strong>{{__('Php')}}</strong>
        </li>
        <li class="border-t py-2 px-3 relative">
            {{__('Php (version :version required)', ['version'=> $phpSupportInfo['minimum']])}}
            <span class="flex justify-center items-center p-2 absolute top-0 right-0 bottom-0 font-bold text-md bg-gray-100">
                <i class="mdi mdi-{{ $phpSupportInfo['supported'] ? 'check-circle-outline text-green-500' : 'alert-circle-outline text-red-500' }} mr-1"></i>
                {{ $phpSupportInfo['current'] }}
            </span>
        </li>
    </ul>
    @foreach($requirements['requirements'] as $type => $requirement)
        <ul class="list-none m-0 p-0 mb-4 border">
            <li class="bg-base-blue text-white p-3">
                <strong>{{__('Php extends')}}</strong>
            </li>
            @foreach($requirements['requirements'][$type] as $extention => $enabled)
                <li class="border-t py-2 px-3 relative">
                    {{ $extention }}
                    <i class="mdi mdi-{{ $enabled ? 'check-circle-outline text-green-500' : 'alert-circle-outline text-red-500' }} absolute top-2 right-3"></i>
                </li>
            @endforeach
        </ul>
    @endforeach

    <ul class="list-none m-0 p-0 mb-4 border">
        <li class="bg-base-blue text-white p-3"><strong>{{ __('Permissions') }}</strong></li>
        @foreach($permissions['permissions'] as $permission)
            <li class="border-t py-2 px-3 relative">
                {{ $permission['folder'] }}
                <span class="flex justify-center items-center p-2 absolute top-0 right-0 bottom-0 font-bold text-md bg-gray-100">
                <i class="mdi mdi-{{ $permission['isSet'] ? 'check-circle-outline text-green-500' : 'alert-circle-outline text-red-500' }} mr-1"></i>
                    {{ $permission['permission'] }}
                </span>
            </li>
        @endforeach
    </ul>

    <ul class="list-none m-0 p-0 mb-4 border">
        <li class="bg-base-blue text-white p-3"><strong>{{ __('Write folders') }}</strong></li>
        @foreach($writeFolders['folders'] as $folder)
            <li class="border-t py-2 px-3 relative">
                {{ $folder['folder'] }}
                <span class="flex justify-center items-center p-2 absolute top-0 right-0 bottom-0 font-bold text-md bg-gray-100">
                    <i class="mdi mdi-{{ $folder['isSet'] ? 'check-circle-outline text-green-500' : 'alert-circle-outline text-red-500' }} mr-1"></i>
                </span>
            </li>
        @endforeach
    </ul>

    @if ( ! isset($requirements['errors']) && $phpSupportInfo['supported'] && ! isset($permissions['errors']) && ! isset($writeFolders['errors']) )
        <div class="text-center mt-10">
            <a class="button blue" href="{{ route('install.environmentWizard') }}">
                {{ __('Set Environment') }}
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif

@endsection
