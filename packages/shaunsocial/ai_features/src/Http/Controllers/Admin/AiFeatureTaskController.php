<?php

namespace Packages\ShaunSocial\AiFeatures\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;
use Packages\ShaunSocial\Core\Models\StorageFile;

class AiFeatureTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.ai_features.manage');
    }

    public function index(Request $request)
    {
        $query = AiFeatureTask::query()->with(['providerKey.provider'])->orderByDesc('id');

        $filters = [
            'status' => $request->get('status', ''),
            'content_type' => $request->get('content_type', ''),
            'provider_key_id' => (int) $request->get('provider_key_id', 0),
            'flagged' => $request->get('flagged', ''),
            'search' => trim((string) $request->get('search', '')),
        ];

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['content_type']) {
            $query->where('content_type', $filters['content_type']);
        }

        if ($filters['provider_key_id']) {
            $query->where('provider_key_id', $filters['provider_key_id']);
        }

        if ($filters['flagged'] === 'yes') {
            $query->where('result->flagged', true);
        } elseif ($filters['flagged'] === 'no') {
            $query->where(function ($sub) {
                $sub->whereNull('result')->orWhere('result->flagged', false);
            });
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($sub) use ($search) {
                $normalizedSearch = strtolower(trim((string) $search));
                $subjectTypeMap = [
                    'post' => 'posts',
                    'posts' => 'posts',
                    'comment' => 'comments',
                    'comments' => 'comments',
                    'comment reply' => 'comment_replies',
                    'comment replies' => 'comment_replies',
                    'comment_reply' => 'comment_replies',
                    'comment_replies' => 'comment_replies',
                    'user' => 'users',
                    'users' => 'users',
                ];
                $subjectTypeMatch = $subjectTypeMap[$normalizedSearch] ?? null;

                if (is_numeric($search)) {
                    $sub->orWhere('id', (int) $search)
                        ->orWhere('subject_id', (int) $search);
                }

                $like = '%'.$search.'%';
                if ($subjectTypeMatch) {
                    $sub->orWhere('subject_type', $subjectTypeMatch);
                }
                $sub->orWhere('subject_type', 'LIKE', $like)
                    ->orWhere('error_message', 'LIKE', $like);
            });
        }

        $tasks = $query->paginate(25)->appends($request->query());

        $statusOptions = [
            '' => __('All statuses'),
            AiFeatureTask::STATUS_PENDING => __('Pending'),
            AiFeatureTask::STATUS_PROCESSING => __('Processing'),
            AiFeatureTask::STATUS_DONE => __('Completed'),
            AiFeatureTask::STATUS_FAILED => __('Failed'),
        ];

        $contentOptions = [
            '' => __('All content types'),
            'text' => __('Text'),
            'image' => __('Image'),
            'video' => __('Video'),
        ];

        $flaggedOptions = [
            '' => __('All moderation results'),
            'yes' => __('Flagged'),
            'no' => __('Not flagged'),
        ];

        $providerOptions = AiProviderKey::orderBy('name')->pluck('name', 'id');

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('AI Feature Tasks'),
            ],
        ];

        $title = __('AI Feature Tasks');

        return view('shaun_ai_features::admin.tasks.index', compact(
            'breadcrumbs',
            'title',
            'tasks',
            'statusOptions',
            'contentOptions',
            'flaggedOptions',
            'providerOptions',
            'filters'
        ));
    }

    public function detail(AiFeatureTask $task)
    {
        $task->load(['providerKey.provider']);

        $mediaPreview = $this->buildMediaPreview($task);
        $backupItems = $mediaPreview ? collect() : $this->buildBackupItems($task);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('AI Feature Tasks'),
                'route' => 'admin.ai_feature.tasks.index',
            ],
            [
                'title' => __('AI Feature Task').' #'.$task->id,
            ],
        ];

        $title = __('AI Feature Task').' #'.$task->id;

        $payloadPretty = json_encode($task->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $resultPretty = json_encode($task->result ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $resultSummary = $task->getResultSummary();

        return view('shaun_ai_features::admin.tasks.detail', compact(
            'breadcrumbs',
            'title',
            'task',
            'payloadPretty',
            'resultPretty',
            'mediaPreview',
            'resultSummary',
            'backupItems'
        ));
    }

    public function retry(Request $request, AiFeatureTask $task): RedirectResponse
    {
        $task->update([
            'status' => AiFeatureTask::STATUS_PENDING,
            'attempts' => 0,
            'processed_at' => null,
            'next_run_at' => Carbon::now(),
            'error_code' => null,
            'error_message' => null,
        ]);

        $request->session()->flash('admin_message_success', __('Task has been queued for processing again.'));

        return redirect()->back();
    }

    public function destroy(Request $request, AiFeatureTask $task): RedirectResponse
    {
        $task->delete();

        $request->session()->flash('admin_message_success', __('Task has been deleted.'));

        return redirect()->route('admin.ai_feature.tasks.index');
    }

    public function destroyMultiple(Request $request): RedirectResponse
    {
        $ids = collect($request->input('selected_tasks', []))
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            $request->session()->flash('admin_message_warning', __('Please select at least one task.'));

            return redirect()->route('admin.ai_feature.tasks.index');
        }

        $deleted = AiFeatureTask::whereIn('id', $ids)->delete();

        if ($deleted > 0) {
            $request->session()->flash('admin_message_success', __('Selected tasks have been deleted.'));
        } else {
            $request->session()->flash('admin_message_warning', __('No tasks were deleted.'));
        }

        return redirect()->route('admin.ai_feature.tasks.index');
    }

    protected function buildMediaPreview(AiFeatureTask $task): ?array
    {
        if (! $task->content_ref_type || ! $task->content_ref_id) {
            return null;
        }

        if (! in_array($task->content_type, ['image', 'video'])) {
            return null;
        }

        $file = findByTypeId($task->content_ref_type, $task->content_ref_id);
        if (! $file instanceof StorageFile) {
            return null;
        }

        $url = AiFeatureTask::resolveStorageUrl($file);
        if (! $url) {
            return null;
        }

        return [
            'type' => $task->content_type,
            'url' => $url,
            'name' => $file->name,
        ];
    }

    protected function buildBackupItems(AiFeatureTask $task): Collection
    {
        $items = $task->getItems();
        $collection = $items instanceof Collection ? $items : collect($items ?: []);

        if ($collection->isEmpty()) {
            return collect();
        }

        $collection->load('storageFile');

        return $collection->map(function ($item) {
            $storageFile = $item->storageFile;
            $item->storage_url = $storageFile ? AiFeatureTask::resolveStorageUrl($storageFile) : null;
            return $item;
        });
    }

}
