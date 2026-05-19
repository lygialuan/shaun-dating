<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_user_page', '1.1.0')) {
            $path = base_path('packages/shaunsocial/user_page/database/sql/install_1.1.0.sql');
            runSqlFile($path);

            //update translate
            $permission = Permission::where('key', 'user_page.allow_create')->first();
            $permission->createTranslationsWithKey('en');

            updatePackageVersion('shaun_user_page', '1.1.0');
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
