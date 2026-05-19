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
        Schema::create('user_list_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->enum('type', [
                'follower',
                'following',
                'subscriber',
                'list'
            ]);
            $table->enum('status', [
                'init',
                'done'
            ])->default('init')->index();
            $table->bigInteger('list_id')->default(0);
            $table->bigInteger('current')->default(0);
            $table->text('content')->nullable();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
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
        Schema::dropIfExists('user_list_messages');
    }
};