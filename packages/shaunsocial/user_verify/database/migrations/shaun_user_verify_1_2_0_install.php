<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_user_verify', '1.2.0')) {
            $path = base_path('packages/shaunsocial/user_verify/database/sql/install_1.2.0.sql');
            runSqlFile($path);

            $permission = Permission::where('key', 'user_verify.send_request')->first();
            $roles = Role::getMemberRoles();
            $permission->createTranslationsWithKey('en');

            foreach ($roles as $role) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'value' => 1
                ]);
            }

            updatePackageVersion('shaun_user_verify', '1.2.0');
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
