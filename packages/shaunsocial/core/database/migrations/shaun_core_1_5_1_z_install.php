<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\MailTemplate;
use Packages\ShaunSocial\Core\Models\TwoFactorProvider;
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
        if (alreadyUpdate('shaun_core', '1.5.1')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.5.1.sql');
            runSqlFile($path);

            updatePackageVersion('shaun_core', '1.5.1');
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
