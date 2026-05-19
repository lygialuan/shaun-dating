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
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->double('amount');
            $table->string('currency');
            $table->enum('status',[
                'init',
                'process',
                'cancel',
                'paid',
                'refund'
            ])->default('init');
            $table->bigInteger('subscription_id')->index();
            $table->text('params')->nullable();
            $table->string('gateway_transaction_id')->index()->nullable();
            
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
        Schema::dropIfExists('subscription_transactions');
    }
};
