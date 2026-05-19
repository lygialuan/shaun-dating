<?php


namespace Packages\ShaunSocial\Vibb\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Traits\Utility;

class Package
{
    use Utility;

    public function install()
    {
        // //add menu
        // $menuArray = [
        //     [
        //         'name' => 'Vibb',
        //         'url' => 'vibb',
        //         'menu_id' => 1,
        //         'is_active' => false,
        //         'icon_default' => 'images/default/menu/vibb.svg',
        //         'type' => 'internal',
        //         'role_access' => '["all"]',
        //         'is_core' => true,
        //         'alias' => 'vibb',
        //         'order' => 4,
        //     ],
        //     [
        //         'name' => 'Vibb',
        //         'url' => 'vibb',
        //         'menu_id' => 2,
        //         'is_active' => false,
        //         'icon_default' => 'images/default/menu/vibb.svg',
        //         'type' => 'internal',
        //         'role_access' => '["all"]',
        //         'is_core' => true,
        //         'alias' => 'vibb',
        //         'order' => 4,
        //     ],
        // ];

        // foreach ($menuArray as $menu) {
        //     MenuItem::create($menu);
        // }

        // // add permission
        // $roles = Role::getMemberRoles();
        // $permissions = Permission::whereIn('key', ['vibb.allow_create', 'vibb.video_max_duration', 'vibb.max_per_day'])->get();
        // foreach ($permissions as $permission) {
        //     $permission->createTranslationsWithKey('en');
            
        //     $value = 0;
        //     switch ($permission->key) {
        //         case 'vibb.allow_create':
        //             $value = 1;
        //             break;
        //         case 'vibb.video_max_duration':
        //             $value = 60;
        //             break;
        //     }
        //     foreach ($roles as $role) {
        //         RolePermission::create([
        //             'role_id' => $role->id,
        //             'permission_id' => $permission->id,
        //             'value' => $value
        //         ]);
        //     }
        // }
    }

    public function afterSaveSetting($setting)
    {
        if ($setting->key == 'shaun_vibb.enable') {
            $active = $setting->value;
            $menuItems = MenuItem::where('alias', 'vibb')->get();
            $menuItems->each(function ($menuItem) use ($active) {
                $menuItem->update(['is_active' => $active]);
            });
        }
    }
}
