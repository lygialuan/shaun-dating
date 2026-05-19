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
        if (alreadyUpdate('shaun_wallet', '1.3.0')) {
            $path = base_path('packages/shaunsocial/wallet/database/sql/install_1.3.0.sql');
            runSqlFile($path);

            updatePackageVersion('shaun_wallet', '1.3.0');
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
