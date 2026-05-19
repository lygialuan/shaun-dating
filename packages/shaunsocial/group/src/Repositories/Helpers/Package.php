<?php


namespace Packages\ShaunSocial\Group\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Group\Models\GroupCategory;

class Package
{
    use Utility;

    public function install()
    {
        // //update translate
        // $categories = GroupCategory::all();
        // $categories->each(function($category) {
        //     $category->createTranslations('en');
        // });

        // // add permission
        // $roles = Role::getMemberRoles();
        // $permissions = Permission::whereIn('key', ['group.allow_create', 'group.max_per_day'])->get();
        // foreach ($permissions as $permission) {
        //     $permission->createTranslationsWithKey('en');
        //     foreach ($roles as $role) {
        //         RolePermission::create([
        //             'role_id' => $role->id,
        //             'permission_id' => $permission->id,
        //             'value' => $permission->key == 'group.allow_create' ? 1 : 0
        //         ]);
        //     }
        // }
        

        // //add menu
        // $menuArray = [
        //     [
        //         'name' => 'Groups',
        //         'url' => 'groups',
        //         'menu_id' => 1,
        //         'is_active' => false,
        //         'icon_default' => 'images/default/menu/group.svg',
        //         'type' => 'internal',
        //         'role_access' => '["all"]',
        //         'is_core' => true,
        //         'alias' => 'group',
        //         'order' => 4,
        //     ],
        //     [
        //         'name' => 'Groups',
        //         'url' => 'Groups',
        //         'menu_id' => 2,
        //         'is_active' => false,
        //         'icon_default' => 'images/default/menu/group.svg',
        //         'type' => 'internal',
        //         'role_access' => '["all"]',
        //         'is_core' => true,
        //         'alias' => 'group',
        //         'order' => 4,
        //     ],
        // ];

        // foreach ($menuArray as $menu) {
        //     MenuItem::create($menu);
        // }
        
        // $data = [
        //     'type' => 'router',
        //     'title' => 'Groups',
        //     'router' => 'group.index'
        // ];
        // $this->createLayoutPage(
        //     $data,
        //     [
        //         'center' => [
        //             [
        //                 'view_type' => 'desktop',
        //                 'type' => 'container',
        //                 'component' => 'Group',
        //                 'title' => 'Container',
        //                 'enable_title' => false,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_group'
        //             ],
        //             [
        //                 'view_type' => 'mobile',
        //                 'type' => 'container',
        //                 'component' => 'Group',
        //                 'title' => 'Container',
        //                 'enable_title' => false,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_group'
        //             ]
        //         ],
        //         'right' => [
        //             [
        //                 'view_type' => 'desktop',
        //                 'type' => 'component',
        //                 'component' => 'GroupNew',
        //                 'title' => 'New Groups',
        //                 'enable_title' => true,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_group',
        //                 'class' => 'Packages\ShaunSocial\Group\Repositories\Helpers\Widget\GroupNewWidget',
        //                 'params' => '{"item_number":"10"}'
        //             ],
        //             [
        //                 'view_type' => 'desktop',
        //                 'type' => 'component',
        //                 'component' => 'GroupPopular',
        //                 'title' => 'Popular Groups',
        //                 'enable_title' => true,
        //                 'role_access' => '["all"]',
        //                 'order' => 2,
        //                 'package' => 'shaun_group',
        //                 'class' => 'Packages\ShaunSocial\Group\Repositories\Helpers\Widget\GroupPopularWidget',
        //                 'params' => '{"item_number":"10"}'
        //             ]
        //         ]
        //     ]
        // );

        // $data = [
        //     'type' => 'router',
        //     'title' => 'Group Profile Page',
        //     'router' => 'group.profile'
        // ];

        // $this->createLayoutPage(
        //     $data,
        //     [
        //         'center' => [
        //             [
        //                 'view_type' => 'desktop',
        //                 'type' => 'container',
        //                 'component' => 'GroupProfile',
        //                 'title' => 'Container',
        //                 'enable_title' => false,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_group'
        //             ],
        //             [
        //                 'view_type' => 'mobile',
        //                 'type' => 'container',
        //                 'component' => 'GroupProfile',
        //                 'title' => 'Container',
        //                 'enable_title' => false,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_group'
        //             ]
        //         ]
        //     ]
        // );
    }

    public function afterSaveSetting($setting)
    {
        if ($setting->key == 'shaun_group.enable') {
            $active = $setting->value;
            $menuItems = MenuItem::where('alias', 'group')->get();
            $menuItems->each(function ($menuItem) use ($active) {
                $menuItem->update(['is_active' => $active]);
            });
        }
    }
}
