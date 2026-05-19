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
        Schema::create('story_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('story_id')->index();
            $table->string('type', 128);
            $table->integer('background_id')->default(0);
            $table->string('content', 1024)->nullable();
            $table->string('content_color')->nullable();
            $table->bigInteger('song_id')->default(0);
            $table->bigInteger('photo_id')->default(0);
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
        Schema::dropIfExists('story_items');
    }
};
