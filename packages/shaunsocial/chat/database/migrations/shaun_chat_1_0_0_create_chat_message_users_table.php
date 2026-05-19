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
        Schema::create('chat_message_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('room_id')->index();
            $table->bigInteger('message_id')->index();
            $table->boolean('is_read')->default(0);
            $table->boolean('is_delete')->default(0);

            $table->index(['room_id', 'is_delete', 'user_id'], 'user_room');
            $table->index(['room_id', 'user_id', 'is_read'],'message_count');
            $table->index(['message_id', 'user_id'], 'messsage_user');

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
        Schema::dropIfExists('chat_message_users');
    }
};
