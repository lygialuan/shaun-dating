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
        Schema::table('users', function (Blueprint $table) {
            $table->double('earn_amount')->default(0);
            $table->double('earn_fee_amount')->default(0);
            $table->integer('paid_content_trial_day')->default(0);
            $table->integer('post_paid_count')->default(0);
            $table->integer('subscriber_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('earn_amount');
            $table->dropColumn('earn_fee_amount');
            $table->dropColumn('paid_content_trial_day');
            $table->dropColumn('post_paid_count');
            $table->dropColumn('subscriber_count');
        });
    }
};