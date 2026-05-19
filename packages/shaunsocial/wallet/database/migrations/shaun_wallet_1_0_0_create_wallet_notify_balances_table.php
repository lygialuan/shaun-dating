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
        Schema::create('wallet_notify_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', [
                'add',
                'reduce'
            ])->default('reduce');
            $table->bigInteger('user_id')->index();
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
        Schema::dropIfExists('wallet_notify_balances');
    }
};
