@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <div class="admin-card-actions">
            <a class="btn-filled-blue admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.ai_provider.keys.create', $provider->id) }}">{{ __('Add API Key') }}</a>
            <a class="btn-filled-white" href="{{ route('admin.ai_provider.index') }}">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="admin-card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <p class="mb-1 text-muted">{{ __('Provider') }}: <strong>{{ $provider->name }}</strong></p>
        <p class="mb-3 text-muted small">{{ __($provider->description) }}</p>

        <div class="table-responsive">
            <table class="admin-table table table-hover">
                <thead>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Active') }}</th>
                    <th>{{ __('Health') }}</th>
                    <th>{{ __('Last error') }}</th>
                    <th style="width: 18%;">{{ __('Action') }}</th>
                </thead>
                <tbody>
                    @forelse ($keys as $key)
                    <tr>    
                        <td>{{ $key->name }}</td>
                        <td>
                            @if ($key->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">
                                    @if ($key->status === 'error')
                                        {{ __('Inactive (auto disabled)') }}
                                    @else
                                        {{ __('Inactive (manual)') }}
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($key->status === 'healthy')
                                <span class="text-success">{{ __('Healthy') }}</span>
                            @else
                                <span class="text-danger">{{ __('Error') }}</span>
                                @if ($key->failure_count)
                                    <div class="small text-muted">
                                        {{ __('Consecutive failures: :count', ['count' => $key->failure_count]) }}
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if ($key->last_error_message)
                                <span class="text-danger">{{ \Illuminate\Support\Str::limit(__($key->last_error_message), 70) }}</span>
                                @if ($key->last_error_at)
                                    <div class="small text-muted">{{ $key->last_error_at->format('Y-m-d H:i') }}</div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.ai_provider.keys.edit', [$provider->id, $key->id]) }}">{{ __('Edit') }}</a>
                            <a class="admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{ route('admin.ai_provider.keys.destroy', $key->id) }}" data-content="{{ __('Are you sure you want to delete this API key?') }}">{{ __('Delete') }}</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">{{ __('No API keys found. Add a key to get started.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/ai_provider.js') }}"></script>
@endpush
