<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\AiFeatures\Repositories\Helpers\Package;


return new class extends Migration
{
    public function up(): void
    {
        if (! alreadyUpdate('shaun_ai_features', '1.0.0')) {
            return;
        }

        $oldChatbotEnable = DB::table('settings')->where('key', 'shaun_chatbot.enable')->value('value');

        $path = base_path('packages/shaunsocial/ai_features/database/sql/install.sql');
        runSqlFile($path);

        if (class_exists(Package::class)) {
            app(Package::class)->install();
        }

        if ($oldChatbotEnable !== null) {
            DB::table('settings')
                ->where('key', 'ai_features.chatbot_enable')
                ->update(['value' => $oldChatbotEnable]);
        }
        
        updatePackageVersion('shaun_ai_features', '1.0.0');
    }

    public function down(): void
    {
        //
    }
};
