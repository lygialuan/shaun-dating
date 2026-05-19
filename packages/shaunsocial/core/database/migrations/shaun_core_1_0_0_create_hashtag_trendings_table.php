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
        Schema::create('hashtag_trendings', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('hashtag_id')->index();
            $table->bigInteger('post_id')->index();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index('created_at');

            $table->index(['post_id', 'name'],'post_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hashtag_trendings');
    }
};
