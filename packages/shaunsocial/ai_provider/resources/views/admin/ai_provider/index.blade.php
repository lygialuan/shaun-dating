@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ __('AI Providers') }}</h5>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('API Keys') }}</th>
                <th style="width: 18%;">{{ __('Action') }}</th>
            </thead>
            <tbody id="ai_provider_list">
                @foreach ($providers as $provider)
                <tr data-id="{{ $provider->id }}">
                    <td>
                        <a href="{{ route('admin.ai_provider.keys.index', $provider->id) }}" class="admin-table-link">
                            {{ $provider->name }}
                        </a>
                    </td>
                    <td class="text-muted small">
                        {{ __($provider->description) }}
                    </td>
                    <td>
                        @if ($provider->keys_count === 0)
                            {{ __('No keys') }}
                        @elseif ($provider->keys_count === 1)
                            {{ __('1 key') }}
                        @else
                            {{ $provider->keys_count }} {{ __('keys') }}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="btn-filled-blue" href="{{ route('admin.ai_provider.keys.index', $provider->id) }}">{{ __('Manage API Keys') }}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/ai_provider.js') }}"></script>
@endpush
