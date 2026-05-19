<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Models\MenuItem;
use Packages\ShaunSocial\Core\Models\Permission;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\RolePermission;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Models\Theme;
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
        if (alreadyUpdate('shaun_core', '1.2.0')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.2.0.sql');
            runSqlFile($path);

            //update translate
            $countries = Country::all();
            $countries->each(function($country) {
                $country->createTranslations('en');
            });

            $states = State::all();
            $states->each(function($state) {
                $state->createTranslations('en');
            });

            $permissions = Permission::whereIn('key', ['post.character_max', 'post.video_max_duration'])->get();
            $roles = Role::getMemberRoles();
            foreach ($permissions as $permission) {
                $permission->createTranslationsWithKey('en');

                foreach ($roles as $role) {
                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'value' => $permission->key == 'post.character_max' ? '63206' : '60'
                    ]);
                }
            }

            updatePackageVersion('shaun_core', '1.2.0');
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
