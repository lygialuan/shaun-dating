<?php

namespace Packages\ShaunSocial\AiProvider\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiProvider\Models\AiProvider;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;

class AiProviderKeyController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.ai_provider.manage');
    }

    public function index(int $providerId)
    {
        $provider = AiProvider::with('keys')->findOrFail($providerId);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('AI Providers'),
                'route' => 'admin.ai_provider.index',
            ],
            [
                'title' => $provider->name,
            ],
        ];

        $title = __('Manage API Keys');
        $keys = $provider->keys()->orderByDesc('id')->get();

        return view('shaun_ai_provider::admin.ai_provider.keys.index', compact('breadcrumbs', 'title', 'provider', 'keys'));
    }

    public function create(int $providerId)
    {
        $provider = AiProvider::findOrFail($providerId);
        $title = __('Add API Key');

        return view('shaun_ai_provider::admin.ai_provider.keys.edit', [
            'title' => $title,
            'provider' => $provider,
            'keyItem' => new AiProviderKey([
                'is_active' => true,
            ]),
            'action' => route('admin.ai_provider.keys.store'),
        ]);
    }

    public function edit(int $providerId, int $keyId)
    {
        $provider = AiProvider::findOrFail($providerId);
        $keyItem = AiProviderKey::where('ai_provider_id', $providerId)->findOrFail($keyId);
        $title = __('Edit API Key');

        return view('shaun_ai_provider::admin.ai_provider.keys.edit', [
            'title' => $title,
            'provider' => $provider,
            'keyItem' => $keyItem,
            'action' => route('admin.ai_provider.keys.store'),
        ]);
    }

    public function store(Request $request)
    {
        $provider = AiProvider::findOrFail((int) $request->ai_provider_id);
        $keyId = $request->id ? (int) $request->id : null;

        $rules = [
            'name' => 'required|max:255',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $config = $request->post('config', []);

        $providerClass = $provider->getClass();
        if (method_exists($providerClass, 'checkConfig')) {
            $result = $providerClass->checkConfig($config);
            if (! $result['status']) {
                return response()->json([
                    'status' => false,
                    'messages' => [
                        $result['message'],
                    ],
                ]);
            }
        }

        $config = array_merge($provider->getDefaultConfig(), $config);

        if (isset($config['temperature'])) {
            $config['temperature'] = (float) $config['temperature'];
        }
        if (isset($config['max_tokens'])) {
            $config['max_tokens'] = (int) $config['max_tokens'];
        }
        if (isset($config['max_output_tokens'])) {
            $config['max_output_tokens'] = (int) $config['max_output_tokens'];
        }

        $isActive = $request->boolean('is_active');

        $data = [
            'ai_provider_id' => $provider->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'config' => $config,
            'is_active' => $isActive,
        ];

        $resetCount = 0;

        if ($keyId) {
            $keyItem = AiProviderKey::where('ai_provider_id', $provider->id)->findOrFail($keyId);
            if ($isActive) {
                $data = array_merge($data, [
                    'status' => 'healthy',
                    'failure_count' => 0,
                    'last_error_message' => null,
                    'last_error_at' => null,
                ]);
            }
            $keyItem->update($data);
        } else {
            $data = array_merge($data, [
                'status' => 'healthy',
                'failure_count' => 0,
                'last_error_message' => null,
                'last_error_at' => null,
            ]);
            $keyItem = AiProviderKey::create($data);
        }

        if ($keyItem->is_active && $keyItem->status === 'healthy') {
            $resetCount = $this->resetFailedTasksForKey($keyItem);
        }

        $request->session()->flash('admin_message_success', __('API Key has been successfully saved.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function destroy(int $keyId)
    {
        $keyItem = AiProviderKey::findOrFail($keyId);
        $providerId = $keyItem->ai_provider_id;

        if (AiProviderKey::isInUse($keyItem->id)) {
            return redirect()
                ->back()
                ->withErrors(__('This AI provider key is currently in use and cannot be deleted.'));
        }

        $keyItem->delete();

        session()->flash('admin_message_success', __('API Key has been deleted.'));

        return redirect()->route('admin.ai_provider.keys.index', ['provider' => $providerId]);
    }

    protected function resetFailedTasksForKey(AiProviderKey $key): int
    {
        $contentTypeSettingMap = [
            'text' => 'ai_features.text_provider_key_id',
            'image' => 'ai_features.image_provider_key_id',
            'video' => 'ai_features.video_provider_key_id',
        ];

        $contentTypes = [];
        foreach ($contentTypeSettingMap as $type => $settingKey) {
            if ((int) setting($settingKey, 0) === $key->id) {
                $contentTypes[] = $type;
            }
        }

        $query = AiFeatureTask::query()
            ->where('status', AiFeatureTask::STATUS_FAILED)
            ->where(function ($inner) use ($key, $contentTypes) {
                $inner->where('provider_key_id', $key->id);
                if (! empty($contentTypes)) {
                    $inner->orWhereIn('content_type', $contentTypes);
                }
            });

        $taskIds = $query->pluck('id');

        if ($taskIds->isEmpty()) {
            return 0;
        }

        AiFeatureTask::whereIn('id', $taskIds)->update([
            'status' => AiFeatureTask::STATUS_PENDING,
            'attempts' => 0,
            'error_code' => null,
            'error_message' => null,
            'next_run_at' => Carbon::now(),
            'processed_at' => null,
            'provider_key_id' => $key->id,
        ]);

        return $taskIds->count();
    }
}
