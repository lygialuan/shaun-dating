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
        Schema::create('user_hashtag_suggests', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('hashtag_id')->index();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index('created_at');
            $table->index(['user_id', 'hashtag_id', 'is_active'], 'user_hashtag_suggest');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_hashtag_suggests');
    }
};
