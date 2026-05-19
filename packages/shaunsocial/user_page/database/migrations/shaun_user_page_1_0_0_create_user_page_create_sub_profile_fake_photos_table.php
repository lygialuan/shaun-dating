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
        Schema::create('user_page_create_sub_profile_fake_photos', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['male', 'female']);
            $table->string('photo', 50);
            $table->bigInteger('user_id')->default(0);
            $table->timestamps();
            $table->index(['gender', 'user_id', 'id'], 'idx_gender_user_id');
        });

        for ($i = 1; $i <= 380; $i++) {
            DB::table('user_page_create_sub_profile_fake_photos')->insert([
                'gender' => 'female',
                'photo' => "f_$i.jpg",
            ]);
        }

        for ($i = 1; $i <= 792; $i++) {
            DB::table('user_page_create_sub_profile_fake_photos')->insert([
                'gender' => 'male',
                'photo' => "m_$i.jpg",
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_page_create_sub_profile_fake_photos');
    }
};
