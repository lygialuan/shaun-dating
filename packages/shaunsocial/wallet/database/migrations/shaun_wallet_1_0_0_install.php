<?php


use Illuminate\Database\Migrations\Migration;
use Packages\ShaunSocial\Core\Models\Language;
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
        if (alreadyUpdate('shaun_wallet', '1.0.0')) {
            $path = base_path('packages/shaunsocial/wallet/database/sql/install.sql');
            runSqlFile($path);

            //update translate
            $mailTemplate = MailTemplate::findByField('type', 'wallet_balance_notify');
            $mailTemplate->createTranslationsWithKey('en');
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
