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
        Schema::create('user_subscription_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('gateway_recurring_id');
            $table->double('amount');
            $table->string('currency');
            $table->enum('status',[
                'init',
                'process',
                'cancel',
                'done',
                'refund'
            ])->default('init');
            $table->bigInteger('package_id')->default(0);
            $table->text('params')->nullable();
            $table->float('exchange');
            $table->string('gateway_recurring_transaction_id')->nullable()->index('usr_sub_orders_grt_id_idx');
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
        Schema::dropIfExists('user_subscription_orders');
    }
};
