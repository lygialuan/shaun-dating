<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_feature_task_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ai_feature_task_id');
            $table->bigInteger('storage_file_id');
            $table->unsignedBigInteger('original_file_id')->nullable();
            $table->string('item_type', 64)->nullable();
            $table->string('item_subject_type', 128)->nullable();
            $table->unsignedBigInteger('item_subject_id')->nullable();
            $table->integer('item_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['ai_feature_task_id', 'item_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_feature_task_items');
    }
};
