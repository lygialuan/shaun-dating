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
        Schema::create('histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
            $table->text('content')->nullable();
            $table->string('mentions', 512)->nullable();
            $table->boolean('is_first')->default(false);
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'],'subject');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
};
