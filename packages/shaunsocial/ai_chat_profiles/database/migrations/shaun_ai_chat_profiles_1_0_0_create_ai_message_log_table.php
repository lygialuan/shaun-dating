<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_message_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('room_id');
            $table->bigInteger('profile_id');
            $table->bigInteger('job_id')->nullable();
            $table->bigInteger('chat_message_id')->nullable();
            $table->string('provider', 32);
            $table->string('model', 64);
            $table->unsignedInteger('tokens_prompt')->nullable();
            $table->unsignedInteger('tokens_completion')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('flagged')->default(false);
            $table->string('flag_reason', 128)->nullable();
            $table->text('prompt_preview')->nullable();
            $table->text('reply_preview')->nullable();
            $table->timestamps();
            $table->index(['profile_id', 'created_at'], 'profile_created');
            $table->index(['flagged', 'created_at'], 'flagged_created');
            $table->index('room_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_message_log');
    }
};
