<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\AiProvider\Repositories\Helpers\Package;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_ai_provider', '1.0.0')) {
            $path = base_path('packages/shaunsocial/ai_provider/database/sql/install.sql');
            runSqlFile($path);
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
