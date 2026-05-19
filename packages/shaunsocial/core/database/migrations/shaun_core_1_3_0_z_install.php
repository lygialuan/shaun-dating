<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;
use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Traits\Utility;

return new class extends Migration
{
    use Utility;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_core', '1.3.0')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.3.0.sql');
            runSqlFile($path);

            //update translate
            $permissions = Permission::whereIn('key', ['post.allow_create_poll', 'post.max_poll_item', 'post.max_close_day'])->get();
            $roles = Role::getMemberRoles();
            foreach ($permissions as $permission) {
                $permission->createTranslationsWithKey('en');
                $value = '1';
                switch ($permission->key) {
                    case 'post.max_poll_item':
                        $value = 5;
                        break;
                    case 'post.max_close_day':
                        $value = 7;
                        break;
                }
                foreach ($roles as $role) {
                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'value' => $value
                    ]);
                }
            }

            $permissions = Permission::whereIn('key', ['post.character_max', 'post.video_max_duration'])->update([
                'type' => 'number'
            ]);
            $categories = ContentWarningCategory::all();
            $categories->each(function($category) {
                $category->createTranslations('en');
            });

            //update menu
            $menuArray = [
                [
                    'name' => 'Documents',
                    'url' => 'documents',
                    'menu_id' => 1,
                    'is_active' => true,
                    'icon_default' => 'images/default/menu/documents.svg',
                    'type' => 'internal',
                    'role_access' => '["all"]',
                    'is_core' => true,
                    'alias' => 'documents',
                    'order' => 4
                ],
                [
                    'name' => 'Documents',
                    'url' => 'documents',
                    'menu_id' => 2,
                    'is_active' => true,
                    'icon_default' => 'images/default/menu/documents.svg',
                    'type' => 'internal',
                    'role_access' => '["all"]',
                    'is_core' => true,
                    'alias' => 'documents',
                    'order' => 4
                ]
            ];

            foreach ($menuArray as $menu) {
                MenuItem::create($menu);
            }

            //create documents page
            $data = [
                'type' => 'router',
                'title' => 'Documents Page',
                'router' => 'document.index'
            ];
            $this->createLayoutPage($data, 
                [
                    'center' => [
                        [
                            'view_type' => 'desktop',
                            'type' => 'container',
                            'component' => 'DocumentsPage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ],
                        [
                            'view_type' => 'mobile',
                            'type' => 'container',
                            'component' => 'DocumentsPage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ]
                    ]
                ]
            );

            updatePackageVersion('shaun_core', '1.3.0');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
