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
        Schema::create('user_page_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_page_id')->index();
            $table->bigInteger('user_id')->default(0);
            $table->string('type', 64)->index();
            $table->string('subject_type', 128)->nullable();
            $table->bigInteger('subject_id')->default(0);
            $table->string('hash', 32)->index();
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
        Schema::dropIfExists('user_page_statistics');
    }
};
