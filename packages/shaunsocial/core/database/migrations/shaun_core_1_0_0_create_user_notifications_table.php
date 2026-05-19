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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('from_id')->index();
            $table->string('subject_type', 128)->nullable();
            $table->bigInteger('subject_id')->nullable();
            $table->string('hash', 32)->index();
            $table->string('class', 512);
            $table->text('params')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->boolean('is_viewed')->default(false);
            $table->string('package', 32)->default('shaun_core');
            $table->boolean('is_system')->default(0);
            $table->timestamps();

            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->index(['user_id', 'from_id'], 'user_from');
            $table->index(['user_id', 'is_viewed'], 'user_viewed');
            $table->index(['id' ,'user_id', 'hash'], 'user');
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
        Schema::dropIfExists('user_notifications');
    }
};
