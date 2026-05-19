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
        Schema::create('vibb_songs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('file_id')->default(0);
            $table->string('name', 512)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('use_count')->default(0)->index();
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
        Schema::dropIfExists('vibb_songs');
    }
};
