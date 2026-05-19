<?php


namespace Packages\ShaunSocial\Advertising\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;

class Package
{
    public function afterSaveSetting($setting)
    {
        if ($setting->key == 'shaun_advertising.enable') {
            $active = $setting->value;
            $menuItems = MenuItem::where('alias', 'advertising')->get();
            $menuItems->each(function ($menuItem) use ($active) {
                $menuItem->update(['is_active' => $active]);
            });
        }
    }

    public function install()
    {
        // //add menu
        // $menuArray = [
        //     [
        //         'name' => 'Ads',
        //         'url' => 'advertisings',
        //         'menu_id' => 1,
        //         'is_active' => false,
        //         'icon_default' => 'images/default/menu/advertising.svg',
        //         'type' => 'internal',
        //         'role_access' => '["all"]',
        //         'is_core' => true,
        //         'alias' => 'advertising',
        //         'order' => 4,
        //     ],
        //     [
        //         'name' => 'Ads',
        //         'url' => 'advertisings',
        //         'menu_id' => 2,
        //         'is_active' => false,
        //         'icon_default' => 'images/default/menu/advertising.svg',
        //         'type' => 'internal',
        //         'role_access' => '["all"]',
        //         'is_core' => true,
        //         'alias' => 'advertising',
        //         'order' => 4,
        //     ],
        // ];

        // foreach ($menuArray as $menu) {
        //     MenuItem::create($menu);
        // }

        // // add permission
        // $roles = Role::all();
        // $permission = Permission::where('key', 'advertising.show_ads')->first();
        // foreach ($roles as $role) {
        //     if ($role->canPermission()) {
        //         RolePermission::create([
        //             'role_id' => $role->id,
        //             'permission_id' => $permission->id,
        //             'value' => 1
        //         ]);
        //     }
        // }
    }
}
