@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
    </div>

    <div class="admin-card-body">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
            <div class="col">
                <div class="admin-stat">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted">{{ __('Status') }}:</span>
                        @php
                            $status = $task->status;
                            $badgeClass = match ($status) {
                                \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_DONE => 'bg-success',
                                \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_FAILED => 'bg-danger',
                                \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_PROCESSING => 'bg-primary',
                                default => 'bg-secondary',
                            };
                            $statusLabel = match ($status) {
                                \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_DONE => __('Completed'),
                                \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_FAILED => __('Failed'),
                                \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_PROCESSING => __('Processing'),
                                default => __('Pending'),
                            };
                        @endphp
                        <span class="fw-semibold">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="admin-stat">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted">{{ __('Content Type') }}:</span>
                        <span class="fw-semibold">{{ ucfirst($task->content_type) }}</span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="admin-stat">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted">{{ __('Attempts') }}:</span>
                        <span class="fw-semibold">{{ $task->attempts }} / {{ $task->max_attempts }}</span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="admin-stat">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted">{{ __('Auto Action (Plan)') }}:</span>
                        <span class="fw-semibold">
                            @switch($task->auto_action)
                                @case(\Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::AUTO_ACTION_FLAG)
                                    {{ __('Flag content') }}
                                    @break
                                @case(\Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::AUTO_ACTION_HIDE)
                                    {{ __('Hide content') }}
                                    @break
                                @case(\Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::AUTO_ACTION_DELETE)
                                    {{ __('Delete content') }}
                                    @break
                                @default
                                    {{ __('None') }}
                            @endswitch
                        </span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="admin-stat">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted">{{ __('Action Taken') }}:</span>
                        <span class="fw-semibold">
                            @if ($task->status === \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_DONE)
                                {{ __('Completed') }}
                            @elseif ($task->status === \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_FAILED)
                                {{ __('Failed after :count attempt(s)', ['count' => $task->attempts]) }}
                            @elseif ($task->status === \Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask::STATUS_PROCESSING)
                                {{ __('Processing') }}
                            @else
                                {{ __('Pending') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        @if ($resultSummary)
            <div class="mb-4">
                <h6 class="mb-3">{{ __('Moderation Result') }}</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge {{ $resultSummary['flagged'] ? 'bg-danger' : 'bg-success' }} me-2">
                        {{ $resultSummary['flagged'] ? __('Flagged') : __('Not flagged') }}
                    </span>
                    @if (! empty($resultSummary['summary']))
                        <span class="text-muted small">{{ $resultSummary['summary'] }}</span>
                    @endif
                </div>

                @if (! empty($resultSummary['reasons']))
                    <div class="mb-3">
                        <div class="fw-semibold mb-1">{{ __('Reasons') }}</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($resultSummary['reasons'] as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($resultSummary['details']))
                    @php
                        $details = $resultSummary['details'];
                        $usage = data_get($details, 'usage');
                    @endphp
                    <div class="row g-3">
                        @if ($model = data_get($details, 'model'))
                            <div class="col-md-4">
                                <div class="small text-muted">{{ __('Model') }}</div>
                                <div>{{ $model }}</div>
                            </div>
                        @endif
                        @if ($tokens = data_get($details, 'usage.total_tokens'))
                            <div class="col-md-4">
                                <div class="small text-muted">{{ __('Total Tokens') }}</div>
                                <div>{{ $tokens }}</div>
                            </div>
                        @endif
                        @if ($promptTokens = data_get($details, 'usage.prompt_tokens'))
                            <div class="col-md-4">
                                <div class="small text-muted">{{ __('Prompt Tokens') }}</div>
                                <div>{{ $promptTokens }}</div>
                            </div>
                        @endif
                        @if ($completionTokens = data_get($details, 'usage.completion_tokens'))
                            <div class="col-md-4">
                                <div class="small text-muted">{{ __('Completion Tokens') }}</div>
                                <div>{{ $completionTokens }}</div>
                            </div>
                        @endif
                    </div>

                    @if ($fullResponse = data_get($details, 'response'))
                        <div class="mt-3">
                            <div class="small text-muted mb-1">{{ __('Provider Raw Response') }}</div>
                            <pre class="bg-light border rounded p-3 small">{{ $fullResponse }}</pre>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        <hr>

        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="mb-2">{{ __('Subject') }}</h6>
                <p class="mb-1">{{ $task->subject_type }} #{{ $task->subject_id }}</p>
                <p class="text-muted small mb-0">{{ __('Queued At') }}: {{ optional($task->created_at)->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->toDayDateTimeString() }}</p>
                <p class="text-muted small mb-0">{{ __('Processed At') }}: {{ $task->processed_at ? $task->processed_at->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->toDayDateTimeString() : __('Not processed') }}</p>
                <p class="text-muted small mb-0">{{ __('Next Run') }}: {{ $task->next_run_at ? $task->next_run_at->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->toDayDateTimeString() : __('Not scheduled') }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">{{ __('Provider Key') }}</h6>
                @if ($task->providerKey)
                    <p class="mb-1">{{ $task->providerKey->name }}</p>
                    <p class="text-muted small mb-0">{{ $task->providerKey->provider?->name }}</p>
                @else
                    <p class="text-muted">{{ __('Not assigned') }}</p>
                @endif
            </div>
        </div>

        <hr>

        <div class="row g-4">
            <div class="col-md-6" data-json-section>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">{{ __('Payload') }}</h6>
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-action="toggle-json"
                        data-target="payload-json-{{ $task->id }}"
                        data-collapsed-label="{{ __('Show JSON') }}"
                        data-expanded-label="{{ __('Hide JSON') }}">
                        {{ __('Show JSON') }}
                    </button>
                </div>
                <div id="payload-json-{{ $task->id }}" class="json-collapse d-none">
                    <pre class="bg-light border rounded p-3 small mb-0">{{ $payloadPretty }}</pre>
                </div>
            </div>
            <div class="col-md-6" data-json-section>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">{{ __('Result (raw)') }}</h6>
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-action="toggle-json"
                        data-target="result-json-{{ $task->id }}"
                        data-collapsed-label="{{ __('Show JSON') }}"
                        data-expanded-label="{{ __('Hide JSON') }}">
                        {{ __('Show JSON') }}
                    </button>
                </div>
                <div id="result-json-{{ $task->id }}" class="json-collapse d-none">
                    <pre class="bg-light border rounded p-3 small mb-0">{{ $resultPretty }}</pre>
                </div>
            </div>
        </div>

        @if ($mediaPreview)
            <hr>
            <div class="mb-4">
                <h6 class="mb-2">{{ __('Referenced Media') }}</h6>
                @if ($mediaPreview['type'] === 'video')
                    <div class="ratio ratio-16x9 mb-2">
                        <video src="{{ $mediaPreview['url'] }}" controls class="w-100 h-100 rounded border"></video>
                    </div>
                @else
                    <div class="mb-2 text-center">
                        <img src="{{ $mediaPreview['url'] }}" alt="{{ $mediaPreview['name'] }}" class="img-fluid rounded border">
                    </div>
                @endif
                <div class="mb-3">
                    <a href="{{ $mediaPreview['url'] }}" target="_blank" rel="noopener" class="btn-text">{{ __('Open original file') }}</a>
                </div>
            </div>
        @elseif ($backupItems->isNotEmpty())
            <hr>
            <div class="mb-4">
                <h6 class="mb-3">{{ __('Referenced Media') }}</h6>
                <div class="row g-3">
                    @foreach ($backupItems as $item)
                        <div class="col-md-4 col-sm-6">
                            <div class="border rounded p-2 text-center h-100">
                                @if ($item->storage_url)
                                    @if (($item->storageFile->type ?? $item->item_type) === 'video')
                                        <video src="{{ $item->storage_url }}" controls class="w-100 rounded"></video>
                                    @else
                                        <img src="{{ $item->storage_url }}" alt="{{ $item->storageFile->name ?? __('Backup item') }}" class="img-fluid rounded">
                                    @endif
                                @else
                                    <span class="text-muted">{{ __('File unavailable') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <hr>
            <div class="alert alert-light border text-muted mb-4">
                {{ __('No media snapshot is available for this task yet.') }}
            </div>
        @endif
    </div>

    <div class="admin-card-footer mt-4">
        <div class="admin-card-bar gap-2 flex-wrap mb-0">
            <a href="{{ route('admin.ai_feature.tasks.index') }}" class="btn-filled-blue">{{ __('Back') }}</a>
            <form method="post" action="{{ route('admin.ai_feature.tasks.retry', $task) }}" class="m-0">
                @csrf
                <button type="submit" class="btn-filled-blue">{{ __('Retry Task') }}</button>
            </form>
            <form method="post" action="{{ route('admin.ai_feature.tasks.destroy', $task) }}" class="m-0" id="ai-feature-task-delete-form">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-filled-red" data-action="delete-task">{{ __('Delete Task') }}</button>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts-body')
<script>
    adminCore?.initConfirmDelete?.();

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-action="toggle-json"]').forEach((button) => {
            const targetId = button.dataset.target;
            const collapsedLabel = button.dataset.collapsedLabel || button.innerText;
            const expandedLabel = button.dataset.expandedLabel || button.innerText;
            const target = targetId ? document.getElementById(targetId) : null;

            if (!target) {
                return;
            }

            const updateLabel = () => {
                const isHidden = target.classList.contains('d-none');
                button.innerText = isHidden ? collapsedLabel : expandedLabel;
            };

            button.addEventListener('click', () => {
                target.classList.toggle('d-none');
                updateLabel();
            });

            updateLabel();
        });

        const deleteButton = document.querySelector('[data-action="delete-task"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', (event) => {
                event.preventDefault();
                const form = document.getElementById('ai-feature-task-delete-form');
                if (!form) {
                    return;
                }
                if (adminCore?.showConfirmModal) {
                    adminCore.showConfirmModal('{{ __("Confirm") }}', '{{ __("Do you want to delete this task?") }}', () => {
                        form.submit();
                    });
                } else if (confirm('{{ __("Do you want to delete this task?") }}')) {
                    form.submit();
                }
            });
        }
    });
</script>
@endpush
