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
        Schema::create('vibb_post_songs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 512)->index();
            $table->bigInteger('song_id')->index();
            $table->bigInteger('post_id')->index();
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
        Schema::dropIfExists('vibb_post_songs');
    }
};
