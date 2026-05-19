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
        Schema::create('dating_swipes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');        
            $table->unsignedBigInteger('target_user_id');  
            $table->enum('action', ['like', 'dislike', 'viewed']);
            $table->timestamps();
            $table->unique(['user_id', 'target_user_id']); 
            $table->index(['user_id', 'action']);
            $table->index(['target_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dating_swipes');
    }
};
