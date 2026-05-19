<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\MailTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (alreadyUpdate('shaun_user_verify', '1.4.0')) {
            $path = base_path('packages/shaunsocial/user_verify/database/sql/install_1.4.0.sql');
            runSqlFile($path);

            //update translate
            $mailTemplate = MailTemplate::findByField('type', 'user_verify_reject');
            $mailTemplate->createTranslationsWithKey('en');

            updatePackageVersion('shaun_user_verify', '1.4.0');
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
