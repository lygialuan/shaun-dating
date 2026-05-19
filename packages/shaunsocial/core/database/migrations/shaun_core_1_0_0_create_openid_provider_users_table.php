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
        Schema::create('openid_provider_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->bigInteger('user_id')->index();
            $table->text('access_token')->nullable();
            $table->string('provider_uid')->index();

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
        Schema::dropIfExists('openid_provider_users');
    }
};
