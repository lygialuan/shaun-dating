<?php


namespace Packages\ShaunSocial\UserPage\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\UserPage\Models\UserPageCategory;

class Package
{
    use Utility;

    public function install()
    {
        //update translate
        $categories = UserPageCategory::all();
        $categories->each(function($category) {
            $category->createTranslations('en');
        });

        //add menu
        $menuArray = [
            [
                'name' => 'Pages',
                'url' => 'pages',
                'menu_id' => 1,
                'is_active' => false,
                'icon_default' => 'images/default/menu/page.svg',
                'type' => 'internal',
                'role_access' => '["all"]',
                'is_core' => true,
                'alias' => 'page',
                'order' => 3,
            ],
            [
                'name' => 'Pages',
                'url' => 'pages',
                'menu_id' => 2,
                'is_active' => false,
                'icon_default' => 'images/default/menu/page.svg',
                'type' => 'internal',
                'role_access' => '["all"]',
                'is_core' => true,
                'alias' => 'page',
                'order' => 3,
            ],
        ];

        // foreach ($menuArray as $menu) {
        //     MenuItem::create($menu);
        // }
        
        $data = [
            'type' => 'router',
            'title' => 'Pages',
            'router' => 'page.index'
        ];
        // $this->createLayoutPage($data, 
        //     [
        //         'top' => [
        //             [
        //                 'view_type' => 'desktop',
        //                 'type' => 'component',
        //                 'component' => 'PageFeature',
        //                 'title' => 'Featured Pages',
        //                 'enable_title' => true,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_user_page',
        //                 'class' => 'Packages\ShaunSocial\UserPage\Repositories\Helpers\Widget\PageFeatureWidget',
        //                 'params' => '{"item_number":"10"}'
        //             ],
        //             [
        //                 'view_type' => 'mobile',
        //                 'type' => 'component',
        //                 'component' => 'PageFeature',
        //                 'title' => 'Featured Pages',
        //                 'enable_title' => true,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_user_page',
        //                 'class' => 'Packages\ShaunSocial\UserPage\Repositories\Helpers\Widget\PageFeatureWidget',
        //                 'params' => '{"item_number":"10"}'
        //             ],
        //         ],
        //         'center' => [
        //             [
        //                 'view_type' => 'desktop',
        //                 'type' => 'container',
        //                 'component' => 'Page',
        //                 'title' => 'Container',
        //                 'enable_title' => false,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_user_page'
        //             ],
        //             [
        //                 'view_type' => 'mobile',
        //                 'type' => 'container',
        //                 'component' => 'Page',
        //                 'title' => 'Container',
        //                 'enable_title' => false,
        //                 'role_access' => '["all"]',
        //                 'order' => 1,
        //                 'package' => 'shaun_user_page'
        //             ]
        //         ]
        //     ]
        // );

        // add permission
        $roles = Role::all();
        $permissions = Permission::whereIn('key', ['user_page.allow_create', 'user_page.max_per_day'])->get();
        foreach ($permissions as $permission) {
            $permission->createTranslationsWithKey('en');
            foreach ($roles as $role) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'value' => $permission->key == 'user_page.allow_create' ? 1 : 0
                ]);
            }
        }
    }

    public function afterSaveSetting($setting)
    {
        if ($setting->key == 'shaun_user_page.enable') {
            $active = $setting->value;
            $menuItems = MenuItem::where('alias', 'page')->get();
            $menuItems->each(function ($menuItem) use ($active) {
                $menuItem->update(['is_active' => $active]);
            });
        }
    }
}
