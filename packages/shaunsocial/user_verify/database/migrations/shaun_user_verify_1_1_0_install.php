<?php


use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_user_verify', '1.1.0')) {
            $path = base_path('packages/shaunsocial/user_verify/database/sql/install_1.1.0.sql');
            runSqlFile($path);

            updatePackageVersion('shaun_user_verify', '1.1.0');
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
