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
        if (alreadyUpdate('shaun_chat', '1.2.0')) {
            $path = base_path('packages/shaunsocial/chat/database/sql/install_1.2.0.sql');
            runSqlFile($path);

            //update translate
            $permission = Permission::where('key', 'chat.allow')->first();
            $permission->createTranslationsWithKey('en');

            updatePackageVersion('shaun_chat', '1.2.0');
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
