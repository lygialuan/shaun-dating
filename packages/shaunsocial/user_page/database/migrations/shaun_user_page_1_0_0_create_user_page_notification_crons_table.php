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
        Schema::create('user_page_notification_crons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_page_id')->index();
            $table->string('type',64);
            $table->bigInteger('current')->default(0);

            $table->index(['user_page_id', 'type']);
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
        Schema::dropIfExists('user_page_notification_crons');
    }
};
