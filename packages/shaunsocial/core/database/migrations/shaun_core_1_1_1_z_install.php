<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Models\MenuItem;
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
        if (alreadyUpdate('shaun_core', '1.1.1')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.1.1.sql');
            runSqlFile($path);

            updatePackageVersion('shaun_core', '1.1.1');
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
