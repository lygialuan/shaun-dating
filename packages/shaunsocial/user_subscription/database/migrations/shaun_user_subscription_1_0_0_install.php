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
        if (alreadyUpdate('shaun_user_subscription', '1.0.0')) {
            $path = base_path('packages/shaunsocial/user_subscription/database/sql/install.sql');
            runSqlFile($path);

            //update translate
            $mailTemplate = MailTemplate::findByField('type', 'user_subscription_active');
            $mailTemplate->createTranslationsWithKey('en');

            $mailTemplate = MailTemplate::findByField('type', 'user_subscription_remind');
            $mailTemplate->createTranslationsWithKey('en');

            $mailTemplate = MailTemplate::findByField('type', 'user_subscription_stop');
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
