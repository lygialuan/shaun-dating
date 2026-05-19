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
        Schema::create('user_page_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_page_id')->index();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('websites')->nullable();
            $table->text('open_hours')->nullable();
            $table->integer('price')->default(0);
            $table->text('description')->nullable();
            $table->boolean('review_enable')->default(true);
            $table->float('review_score')->default(0);
            $table->bigInteger('review_count')->default(0);
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
        Schema::dropIfExists('user_page_infos');
    }
};
