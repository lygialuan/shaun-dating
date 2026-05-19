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
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description', 512)->nullable()->default(null);
            $table->string('key')->unique();
            $table->boolean('is_support_guest')->default(false);
            $table->boolean('is_support_moderator')->default(false);
            $table->unsignedInteger('group_id')->index();
            $table->enum('type', ['text', 'checkbox']);
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
        Schema::dropIfExists('permissions');
    }
};
