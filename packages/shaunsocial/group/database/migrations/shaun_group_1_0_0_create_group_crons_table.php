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
        Schema::create('group_crons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->index();
            $table->bigInteger('user_id')->default(0);
            $table->bigInteger('item_id')->default(0)->index();
            $table->bigInteger('group_id')->index();
            $table->bigInteger('current')->default(0);
            $table->text('params')->nullable();
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
        Schema::dropIfExists('group_crons');
    }
};
