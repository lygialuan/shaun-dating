@extends('shaun_core::admin.layouts.master')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ __('AI Feature Tasks') }}</h5>
    </div>

    <form method="get" class="mb-3">
        <div class="admin-card-bar gap-2 flex-wrap">
            <div class="d-flex flex-wrap gap-2 w-100">
                <select name="status" class="form-select" style="min-width: 160px;">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="content_type" class="form-select" style="min-width: 160px;">
                    @foreach ($contentOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['content_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="provider_key_id" class="form-select" style="min-width: 180px;">
                    <option value="0">{{ __('All providers') }}</option>
                    @foreach ($providerOptions as $id => $name)
                        <option value="{{ $id }}" @selected($filters['provider_key_id'] === (int) $id)>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="flagged" class="form-select" style="min-width: 160px;">
                    @foreach ($flaggedOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['flagged'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex flex-wrap gap-2 w-100 align-items-start">
                <div class="d-flex flex-column" style="max-width: 460px;">
                    <div class="admin-card-search-bar-wrap">
                        <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                        <input
                            type="text"
                            name="search"
                            class="admin-card-search-bar-input form-control"
                            placeholder="{{ __('Search tasks') }}"
                            value="{{ $filters['search'] }}"
                        >
                    </div>
                    <small class="text-muted mt-1">{{ __('Search by task ID, subject type, or error message.') }}</small>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-filled-blue">{{ __('Search') }}</button>
                    <button type="button" class="btn-filled-blue" onclick="window.location='{{ route('admin.ai_feature.tasks.index') }}'">
                        {{ __('Reset') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="admin-card-body table-responsive">
        <form id="ai-feature-task-bulk-form" method="post" action="{{ route('admin.ai_feature.tasks.destroy_multiple') }}">
            @csrf
            @method('DELETE')
        </form>

        <table class="admin-table table table-hover align-middle">
            <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" class="form-check-input" data-toggle="select-all" data-target=".task-select">
                    </th>
                    <th width="70">{{ __('ID') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Content Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Provider Key') }}</th>
                    <th>{{ __('Flagged') }}</th>
                    <th>{{ __('Reported') }}</th>
                    <th>{{ __('AI Check At') }}</th>
                    <th style="width: 140px;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    @php
                        $isFlagged = (bool) data_get($task->result, 'flagged', false);
                        $statusLabel = $statusOptions[$task->status] ?? ucfirst($task->status);
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input task-select" name="selected_tasks[]" value="{{ $task->id }}" form="ai-feature-task-bulk-form">
                        </td>
                        <td>#{{ $task->id }}</td>
                        @php
                            $subjectLabel = $task->getSubjectLabel();
                            $subjectHref = $task->getHref();
                        @endphp
                        <td>
                            <div class="small text-muted">{{ $subjectLabel }}</div>
                            @if ($subjectHref)
                                <a href="{{ $subjectHref }}" target="_blank" rel="noopener" class="text-decoration-none">
                                    #{{ $task->subject_id }}
                                </a>
                            @else
                                <div>#{{ $task->subject_id }}</div>
                            @endif
                        </td>
                        <td>{{ ucfirst($task->content_type) }}</td>
                        <td>
                            @switch($task->status)
                                @case(\Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_DONE)
                                    <span class="badge bg-success">{{ $statusLabel }}</span>
                                    @break
                                @case(\Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_FAILED)
                                    <span class="badge bg-danger">{{ $statusLabel }}</span>
                                    @break
                                @case(\Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_PROCESSING)
                                    <span class="badge bg-warning text-dark">{{ $statusLabel }}</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $statusLabel }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if ($task->providerKey)
                                {{ $task->providerKey->name }}
                                <div class="small text-muted">{{ $task->providerKey->provider?->name }}</div>
                            @else
                                <span class="text-muted">{{ __('Not assigned') }}</span>
                            @endif
                            @if ($task->error_message)
                                <div class="small text-danger mt-1" title="{{ $task->error_message }}">
                                    {{ Str::limit(__($task->error_message), 80) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if ($isFlagged)
                                <span class="badge bg-danger">{{ __('Violated') }}</span>
                            @elseif (data_get($task->result, 'flagged') === false)
                                <span class="badge bg-success">{{ __('Not violated') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($task->reported_at)
                                <span class="badge bg-info">{{ __('Reported') }}</span>
                            @else
                                <span class="text-muted">{{ __('None') }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ optional($task->updated_at)->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->diffForHumans() }}
                        </td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.ai_feature.tasks.show', $task) }}" class="btn-text">{{ __('View Details') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">{{ __('No tasks found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $tasks->links('shaun_core::admin.partial.paginate') }}

        <div class="mt-3">
            <button type="submit" form="ai-feature-task-bulk-form" class="btn-filled-red" data-action="bulk-delete">
                {{ __('Delete') }}
            </button>
        </div>
    </div>
</div>
@stop

@push('scripts-body')
<script>
    adminCore?.initConfirmDelete?.();

    document.addEventListener('DOMContentLoaded', () => {
        const toggleAll = document.querySelector('[data-toggle="select-all"]');
        const bulkCheckboxes = Array.from(document.querySelectorAll('.task-select'));
        const bulkButton = document.querySelector('[data-action="bulk-delete"]');

        if (toggleAll) {
            toggleAll.addEventListener('change', () => {
                bulkCheckboxes.forEach((checkbox) => {
                    checkbox.checked = toggleAll.checked;
                });
            });
        }

        bulkCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                if (!toggleAll) {
                    return;
                }
                const allChecked = bulkCheckboxes.length > 0 && bulkCheckboxes.every((item) => item.checked);
                toggleAll.checked = allChecked;
            });
        });

        if (bulkButton) {
            bulkButton.addEventListener('click', (event) => {
                const form = document.getElementById('ai-feature-task-bulk-form');
                if (!form) {
                    return;
                }
                const selected = document.querySelectorAll('.task-select:checked');
                if (!selected.length) {
                    event.preventDefault();
                    return;
                }
                event.preventDefault();
                if (adminCore?.showConfirmModal) {
                    adminCore.showConfirmModal('{{ __("Confirm") }}', '{{ __("Do you want to delete selected tasks?") }}', () => {
                        form.submit();
                    });
                } else if (confirm('{{ __("Do you want to delete selected tasks?") }}')) {
                    form.submit();
                }
            });
        }
    });
</script>
@endpush
