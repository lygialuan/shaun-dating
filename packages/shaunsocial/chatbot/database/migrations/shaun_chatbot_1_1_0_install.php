<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Chatbot\Repositories\Helpers\Package;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_chatbot', '1.1.0')) {
            $path = base_path('packages/shaunsocial/chatbot/database/sql/install_1.1.0.sql');
            runSqlFile($path);
            updatePackageVersion('shaun_chatbot', '1.1.0');
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
