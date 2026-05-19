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
        Schema::create('user_subscription_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->double('amount');
            $table->integer('package_id')->index();
            $table->integer('trial_day')->default(0);
            $table->integer('billing_cycle');
            $table->enum('billing_cycle_type', [
                'day',
                'week',
                'month',
                'year'
            ]);
            $table->integer('order')->default(0);
            $table->string('google_price_id')->nullable();
            $table->string('apple_price_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->integer('gateway_recurring_id')->default(0);
            $table->string('flex_form_id')->nullable();
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
        Schema::dropIfExists('user_subscription_plans');
    }
};
