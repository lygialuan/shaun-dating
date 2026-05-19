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
        Schema::create('story_backgrounds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('photo_id')->default(0);
            $table->boolean('is_core')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
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
        Schema::dropIfExists('story_backgrounds');
    }
};
