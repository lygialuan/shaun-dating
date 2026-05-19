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
        Schema::table('user_follows', function (Blueprint $table) {
            $table->boolean('user_is_page')->default(false)->index();
            $table->boolean('follower_is_page')->default(false)->index();
            $table->index(['user_id', 'follower_id', 'user_is_page'], 'user_page');
            $table->index(['user_id', 'follower_id', 'follower_is_page'], 'follower_page');
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
