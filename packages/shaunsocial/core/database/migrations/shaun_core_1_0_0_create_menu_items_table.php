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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->integer('menu_id')->index();
            $table->integer('parent_id')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_new_tab')->default(false);
            $table->boolean('is_header')->default(false);
            $table->string('icon_file_id')->nullable();
            $table->string('icon_default')->nullable();
            $table->enum('type', ['internal', 'outbound']);
            $table->string('role_access')->nullable();
            $table->boolean('is_core')->default(false);
            $table->string('alias', 32)->nullable();
            $table->integer('order')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
};
