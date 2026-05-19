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
        Schema::create('gift_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('gift_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('total_price');
            $table->string('target_type'); //profile, post...
            $table->unsignedBigInteger('target_id'); //user_id, post_id,...
            $table->timestamps();
            $table->index(['sender_id']);
            $table->index(['receiver_id']);
            $table->index(['target_type','target_id']);
            $table->index(['gift_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_transactions');
    }
};
