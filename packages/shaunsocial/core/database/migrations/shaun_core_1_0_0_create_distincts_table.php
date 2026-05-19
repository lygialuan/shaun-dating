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
        Schema::create('distincts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',32);
            $table->bigInteger('user_id');
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
            $table->string('hash',32)->index();
            $table->string('user_hash',32)->index();

            $table->timestamp('updated_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('distincts');
    }
};
