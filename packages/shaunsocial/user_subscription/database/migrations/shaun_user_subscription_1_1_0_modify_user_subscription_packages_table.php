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
        Schema::table('user_subscription_packages', function (Blueprint $table) {
            $table->string('badge_background_color', 32)->default('#FFFFFF')->nullable();
            $table->string('badge_text_color', 32)->default('#000000')->nullable();
            $table->string('badge_border_color', 32)->default('#CCCCCC')->nullable();
            $table->boolean('is_show_badge')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
};
