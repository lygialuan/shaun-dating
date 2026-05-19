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
        if (alreadyUpdate('shaun_vibb', '1.1.0')) {
            $path = base_path('packages/shaunsocial/vibb/database/sql/install_1.1.0.sql');
            runSqlFile($path);

            //update translate
            $permissions = Permission::whereIn('key', ['vibb.max_per_day'])->get();
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

            updatePackageVersion('shaun_vibb', '1.1.0');
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
