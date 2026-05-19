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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->string('type')->index();
            $table->string('type_extra')->nullable();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
            $table->text('params')->nullable();
            $table->double('amount');
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->index(['subject_type', 'subject_id'], 'subject');
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
        Schema::dropIfExists('wallet_transactions');
    }
};
