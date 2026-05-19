<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Packages\ShaunSocial\Core\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        if (! alreadyUpdate('shaun_chatbot', '1.2.0')) {
            return;
        }

        DB::table('setting_group_subs')->where('key', 'chatbot')->delete();
        DB::table('settings')->where('key', 'shaun_chatbot.enable')->delete();
        DB::table('permissions')->where('key', 'admin.chatbot.manage')->delete();

        Schema::dropIfExists('chatbot_providers');

        updatePackageVersion('shaun_chatbot', '1.2.0');
    }

    public function down(): void
    {
        // no-op
    }
};
