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
        if (alreadyUpdate('shaun_core', '1.5.0')) {
            $path = base_path('packages/shaunsocial/core/database/sql/install_1.5.0.sql');
            runSqlFile($path);

            $languages = Language::all();
            $languages->each(function($language) {
                $language->createTranslations();
            });

            $mailTempletes = MailTemplate::where('type', 'two_factory_send_code')->get();
            
            foreach ($mailTempletes as $mailTemplete) {
                $mailTemplete->createTranslationsWithKey('en');
            }

            $providers = TwoFactorProvider::all();
            foreach ($providers as $provider) {
                $provider->createTranslationsWithKey('en');
            }

            updatePackageVersion('shaun_core', '1.5.0');
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
