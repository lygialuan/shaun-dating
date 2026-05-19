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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
            $table->double('amount');
            $table->string('currency');
            $table->bigInteger('gateway_id');
            $table->string('type')->index();
            $table->text('params')->nullable();
            $table->integer('billing_cycle');
            $table->enum('billing_cycle_type', [
                'day',
                'week',
                'month',
                'year'
            ]);
            $table->enum('status', [
                'init',
                'active',
                'cancel',
                'stop'
            ]);
            $table->string('package_type', 128);
            $table->bigInteger('package_id');
            $table->string('package_name');
            $table->dateTime('expired_at')->nullable()->index();
            $table->dateTime('reminded_at')->nullable()->index();
            $table->boolean('reminded')->default(false)->index();
            $table->boolean('first_time_active')->default(true);
            $table->integer('remind_day')->default(0);
            $table->bigInteger('gateway_recurring_id')->default(0)->index();
            $table->string('gateway_recurring_transaction_id')->index()->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->index(['package_type', 'package_id'], 'package');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
