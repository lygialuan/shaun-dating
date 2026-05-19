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
        if (alreadyUpdate('shaun_chat', '1.4.0')) {
            $path = base_path('packages/shaunsocial/chat/database/sql/install_1.4.0.sql');
            runSqlFile($path);
            
            //update translate
            $permission = Permission::where('key', 'chat.send_audio_max_duration')->first();
            $permission->createTranslationsWithKey('en');

            $roles = Role::getMemberRoles();
            foreach ($roles as $role) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'value' => '120'
                ]);
            }
            
            updatePackageVersion('shaun_chat', '1.4.0');
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
