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
        Schema::create('user_page_create_sub_profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->default(0);
            $table->integer('number_of_users')->default(1);
            $table->unsignedBigInteger('expire_role_id')->nullable();
            $table->unsignedBigInteger('gender_id')->default(0);
            $table->integer('from_age')->default(0);
            $table->integer('to_age')->default(0);
            $table->text('about_me')->nullable(); 
            $table->text('interests')->nullable(); 
            $table->unsignedBigInteger('country_id')->default(0);
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->boolean('is_created')->default(false);
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
        Schema::dropIfExists('user_page_create_sub_profiles');
    }
};
