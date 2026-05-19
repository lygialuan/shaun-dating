<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Group\Repositories\Helpers\Package;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_group', '1.0.0')) {
            $path = base_path('packages/shaunsocial/group/database/sql/install.sql');
            runSqlFile($path);

            //run install
            $package = app(Package::class);
            $package->install();
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
