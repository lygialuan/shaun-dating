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
        Schema::create('user_post_paid_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('post_id');
            $table->bigInteger('post_owner_id');
            $table->bigInteger('gateway_id');
            $table->bigInteger('package_id')->default(0);
            $table->text('params')->nullable();
            $table->string('gateway_transaction_id')->index()->nullable();
            $table->double('amount');
            $table->string('currency');
            $table->enum('status',[
                'init',
                'done',
            ])->default('init')->index();
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
        Schema::dropIfExists('user_post_paid_orders');
    }
};