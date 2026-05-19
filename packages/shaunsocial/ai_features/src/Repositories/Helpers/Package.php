<?php

namespace Packages\ShaunSocial\AiFeatures\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\MenuItem;

class Package
{
    public function install(): void
    {
        
    }

    public function afterSaveSetting($setting)
    {
        if ($setting->key == 'ai_features.chatbot_enable') {
            $active = $setting->value;
            $menuItems = MenuItem::where('alias', 'chatbot')->get();
            $menuItems->each(function ($menuItem) use ($active) {
                $menuItem->update(['is_active' => $active]);
            });
        }
    }
}
