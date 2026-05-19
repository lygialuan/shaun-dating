<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_persona_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('profile_id')->unique();
            $table->boolean('enabled')->default(false);
            $table->string('tone', 16)->default('friendly');
            $table->string('intent', 16)->default('casual');
            $table->unsignedTinyInteger('trait_playfulness')->default(50);
            $table->unsignedTinyInteger('trait_warmth')->default(50);
            $table->unsignedTinyInteger('trait_assertiveness')->default(50);
            $table->string('message_length', 16)->default('medium');
            $table->unsignedTinyInteger('max_messages_per_day')->default(50);
            $table->unsignedTinyInteger('reply_delay_min_sec')->default(10);
            $table->unsignedTinyInteger('reply_delay_max_sec')->default(120);
            $table->text('custom_system_prompt')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_persona_configs');
    }
};
