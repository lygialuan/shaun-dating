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
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->string('type', 128)->index();
            $table->text('content')->nullable();
            $table->string('hashtags', 1028)->nullable()->fulltext();
            $table->bigInteger('like_count')->default(0);
            $table->bigInteger('comment_count')->default(0);
            $table->bigInteger('parent_id')->default(0);
            $table->string('mentions', 512)->nullable();
            $table->text('content_search')->nullable()->fulltext();
            $table->integer('user_privacy')->default(1);
            $table->boolean('is_edited')->default(false);
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
        Schema::dropIfExists('posts');
    }
};
