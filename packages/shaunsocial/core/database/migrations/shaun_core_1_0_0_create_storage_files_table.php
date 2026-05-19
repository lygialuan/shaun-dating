<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_files', function (Blueprint $table) {
            $table->id();
            $table->string('service_key', 16)->index();
            $table->bigInteger('user_id')->default(0)->index();
            $table->bigInteger('parent_file_id')->default(0)->index();
            $table->bigInteger('parent_id')->default(0)->index();
            $table->string('parent_type', 128)->index();
            $table->string('type', 128)->default('')->index();
            $table->string('storage_path');
            $table->string('extension', 16);
            $table->string('name');
            $table->bigInteger('size');
            $table->integer('order')->default(1);
            $table->boolean('has_child')->default(false);
            $table->text('params')->nullable()->default(null);
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storage_files');
    }
};
