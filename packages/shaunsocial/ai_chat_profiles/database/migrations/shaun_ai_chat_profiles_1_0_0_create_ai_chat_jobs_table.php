<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_chat_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('room_id');
            $table->bigInteger('profile_id');
            $table->bigInteger('sender_id');
            $table->bigInteger('trigger_message_id');
            $table->bigInteger('reply_message_id')->nullable();
            $table->string('status', 16)->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_at'], 'status_scheduled');
            $table->index(['profile_id', 'status'], 'profile_status');
            $table->index('room_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_chat_jobs');
    }
};
