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
        if (alreadyUpdate('shaun_wallet', '1.1.0')) {
            $path = base_path('packages/shaunsocial/wallet/database/sql/install_1.1.0.sql');
            runSqlFile($path);

            $permissions = Permission::whereIn('key', ['wallet.transfer_fund', 'wallet.send_fund'])->get();
            foreach ($permissions as $permission) {
                $permission->createTranslationsWithKey('en');
            }

            updatePackageVersion('shaun_wallet', '1.1.0');
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
