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
        if (alreadyUpdate('shaun_dating', '1.7.0')) {
            $path = base_path('packages/shaunsocial/dating/database/sql/install_1.7.0.sql');
            runSqlFile($path);

            updatePackageVersion('shaun_dating', '1.7.0');
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
