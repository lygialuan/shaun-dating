<?php

namespace Packages\ShaunSocial\AiChatProfiles\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatIntent;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatMode;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiChatTone;
use Packages\ShaunSocial\AiChatProfiles\Enums\AiMessageLength;
use Packages\ShaunSocial\AiChatProfiles\Models\AiPersonaConfig;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;

class AiPersonaConfigController extends Controller
{
    public function __construct()
    {
        if (!setting('ai_chat_profiles.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }
        $this->middleware('has.permission:admin.user_page.manage');
    }

    public function edit(int $id)
    {
        $page   = User::where('is_page', true)->findOrFail($id);
        $config = AiPersonaConfig::firstOrNew(['profile_id' => $id]);

        $title = __('AI Config - :name', ['name' => $page->name]);
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Profiles'),
                'route' => 'admin.user_page.index',
            ],
            [
                'title' => $title
            ],
        ];

        return view('shaun_ai_chat_profiles::admin.ai_persona_config.edit', [
            'page'       => $page,
            'config'     => $config,
            'tones'      => AiChatTone::cases(),
            'intents'    => AiChatIntent::cases(),
            'modes'      => AiChatMode::cases(),
            'lengths'    => AiMessageLength::cases(),
            'breadcrumbs'=> $breadcrumbs,
            'title'      => $title
        ]);
    }

    public function store(Request $request, int $id)
    {
        User::where('is_page', true)->findOrFail($id);

        $data = $request->validate([
            'tone'                 => 'required|in:flirty,friendly,playful,reserved',
            'intent'               => 'required|in:serious,casual,friendship',
            'trait_playfulness'    => 'required|integer|min:0|max:100',
            'trait_warmth'         => 'required|integer|min:0|max:100',
            'trait_assertiveness'  => 'required|integer|min:0|max:100',
            'message_length'       => 'required|in:short,medium,long',
            'max_messages_per_day' => 'required|integer|min:1|max:1000',
            'reply_delay_min_sec'  => 'required|integer|min:1|max:1000',
            'reply_delay_max_sec'  => 'required|integer|min:1|max:1000',
            'custom_system_prompt' => 'nullable|string|max:2000',
            'enabled'              => 'nullable|boolean',
        ]);

        $data['enabled'] = (bool) ($data['enabled'] ?? false);

        AiPersonaConfig::updateOrCreate(['profile_id' => $id], $data);

        return redirect()->route('admin.user_page.ai_config.edit', $id)->with('success', __('AI config saved successfully.'));
    }
}
