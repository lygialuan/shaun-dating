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
        Schema::create('plan_gateway_recurrings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('plan_id')->index();
            $table->bigInteger('gateway_recurring_id')->index();
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
        Schema::dropIfExists('plan_gateway_recurrings');
    }
};
