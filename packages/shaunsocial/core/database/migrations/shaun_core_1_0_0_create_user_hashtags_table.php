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
        Schema::create('user_hashtags', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');            
            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->text('hashtags', 1028)->nullable()->fulltext();
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
        Schema::dropIfExists('user_hashtags');
    }
};
