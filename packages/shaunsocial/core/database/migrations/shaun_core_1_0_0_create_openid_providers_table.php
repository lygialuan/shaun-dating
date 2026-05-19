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
        Schema::create('openid_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('app_name')->index();
            $table->bigInteger('photo_id')->default(0);
            $table->string('photo_default')->nullable();
            $table->string('server');
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('scope')->nullable();
            $table->string('authorize_endpoint');
            $table->string('access_token_endpoint');
            $table->string('get_user_info_endpoint');
            $table->boolean('is_active')->default(true);
            $table->string('user_id_map');
            $table->string('email_map')->nullable();
            $table->string('name_map');
            $table->string('avatar_map')->nullable();
            $table->boolean('is_core')->default(0);
            $table->integer('order')->default(0);
            
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
        Schema::dropIfExists('openid_providers');
    }
};
