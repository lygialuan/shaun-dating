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
        Schema::create('code_verifies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 64);
            $table->bigInteger('user_id');
            $table->string('email')->nullable();
            $table->string('code', 16)->index();
            $table->index(['type', 'user_id', 'code'], 'user_type_code');
            $table->index(['type', 'user_id'], 'user_type');
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
        Schema::dropIfExists('code_verifies');
    }
};
