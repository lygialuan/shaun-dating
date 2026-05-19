<?php

namespace Packages\ShaunSocial\Chatbot\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Models\Setting;
use Packages\ShaunSocial\Core\Traits\Utility;

class Package
{
    use Utility;

    public function install(): void
    {
        // add menu
        $menuArray = [
            [
                'name' => 'Chatbot',
                'url' => 'chatbot',
                'menu_id' => 1,
                'is_active' => false,
                'icon_default' => 'images/default/menu/chatbot.svg',
                'type' => 'internal',
                'role_access' => '["all"]',
                'is_core' => true,
                'alias' => 'chatbot',
                'order' => 4,
            ],
            [
                'name' => 'Chatbot',
                'url' => 'chatbot',
                'menu_id' => 2,
                'is_active' => false,
                'icon_default' => 'images/default/menu/chatbot.svg',
                'type' => 'internal',
                'role_access' => '["all"]',
                'is_core' => true,
                'alias' => 'chatbot',
                'order' => 4,
            ],
        ];

        foreach ($menuArray as $menu) {
            MenuItem::create($menu);
        }

        // add permission defaults
        $roles = Role::getMemberRoles();
        $permissions = Permission::whereIn('key', ['chatbot.limit_message_per_day', 'chatbot.character_max'])->get();
        foreach ($permissions as $permission) {
            $permission->createTranslationsWithKey('en');

            foreach ($roles as $role) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'value' => $permission->key === 'chatbot.limit_message_per_day' ? 100 : 200,
                ]);
            }
        }

    }

    
}
