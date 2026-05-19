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
        Schema::create('user_page_follow_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_page_id')->index();
            $table->bigInteger('user_id');
            $table->integer('gender_id')->index();
            $table->integer('birthday')->default(0)->index();
            $table->timestamps();
            $table->index(['user_page_id','user_id'], 'user_page_follower');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_page_follow_reports');
    }
};
