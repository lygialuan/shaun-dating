<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_feature_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject_type', 128);
            $table->unsignedBigInteger('subject_id');
            $table->string('content_type', 32);
            $table->string('content_ref_type', 128)->nullable();
            $table->unsignedBigInteger('content_ref_id')->nullable();
            $table->unsignedBigInteger('provider_key_id')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->timestamp('next_run_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->string('auto_action', 32)->default('none');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['content_ref_type', 'content_ref_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_feature_tasks');
    }
};
