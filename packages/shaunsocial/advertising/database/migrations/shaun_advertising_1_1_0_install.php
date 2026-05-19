<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Advertising\Repositories\Helpers\Package;
use Packages\ShaunSocial\Core\Models\MenuItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_advertising', '1.1.0')) {
            $path = base_path('packages/shaunsocial/advertising/database/sql/install_1.1.0.sql');
            runSqlFile($path);

            updatePackageVersion('shaun_advertising', '1.1.0');
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

