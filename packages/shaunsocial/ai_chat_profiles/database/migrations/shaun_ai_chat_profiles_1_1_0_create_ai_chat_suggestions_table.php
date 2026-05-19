<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_suggestions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('profile_id'); // subprofile that triggered
            $table->unsignedBigInteger('user_id'); // real user receiving suggestion
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('chat_message_id')->nullable(); // trigger message
            $table->text('suggestion_text');
            $table->timestamps();

            $table->index(['room_id', 'user_id']);
            $table->index('job_id');
            $table->foreign('job_id')->references('id')->on('ai_chat_jobs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_suggestions');
    }
};

