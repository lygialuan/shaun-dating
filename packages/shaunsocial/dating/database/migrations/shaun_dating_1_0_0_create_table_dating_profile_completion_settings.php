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
        Schema::create('dating_profile_completion_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active');
            $table->unsignedTinyInteger('basic_info')->default(0);
            $table->unsignedTinyInteger('about')->default(0);
            $table->unsignedTinyInteger('profile_verification')->default(0);
            $table->unsignedTinyInteger('work_education')->default(0);
            $table->unsignedTinyInteger('more_about')->default(0);
            $table->unsignedTinyInteger('interests')->default(0);
            $table->unsignedTinyInteger('social_profiles')->default(0);

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
        Schema::dropIfExists('dating_profile_completion_settings');
    }
};
