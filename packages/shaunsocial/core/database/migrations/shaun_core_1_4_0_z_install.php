<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
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
        if (alreadyUpdate('shaun_core', '1.4.0')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.4.0.sql');
            runSqlFile($path);

            $data = [
                'type' => 'router',
                'title' => 'Custom Landing Page',
                'router' => 'custom_landing_page.index'
            ];
            $this->createLayoutPage($data, 
                [
                    'center' => [
                        [
                            'view_type' => 'desktop',
                            'type' => 'container',
                            'component' => 'CustomLandingPage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ],
                        [
                            'view_type' => 'mobile',
                            'type' => 'container',
                            'component' => 'CustomLandingPage',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'package' => 'shaun_core'
                        ]
                    ]
                ]
            );

            //update translate
            $permissions = Permission::whereIn('key', ['post.max_per_day', 'post.comment_max_per_day'])->get();
            $roles = Role::getMemberRoles();
            foreach ($permissions as $permission) {
                $permission->createTranslationsWithKey('en');
                foreach ($roles as $role) {
                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'value' => 0
                    ]);
                }
            }

            updatePackageVersion('shaun_core', '1.4.0');
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
