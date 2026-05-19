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
        Schema::create('chat_room_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('room_id');
            $table->bigInteger('user_id')->index();
            $table->timestamp('last_updated_at')->index()->useCurrent();
            $table->enum('status',['created', 'sent', 'cancelled', 'accepted'])->default('created');
            $table->integer('message_count')->default(0);
            $table->boolean('enable_notify')->default(true);
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_moderator')->default(false);
            $table->string('user_name');

            $table->index(['user_id', 'status', 'last_updated_at'],'user_status');
            $table->index(['room_id', 'user_id', 'status', 'message_count','enable_notify'],'user_chat_count');
            $table->index(['user_id', 'status', 'room_id', 'user_name', 'last_updated_at'],'user_search');
            $table->index(['user_id', 'status'],'user_search_status');
            
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
        Schema::dropIfExists('chat_room_members');
    }
};
