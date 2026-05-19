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
        Schema::create('sync_old_users', function (Blueprint $table) {
            $table->id();
            $table->enum('status', [
                'pending',
                'processing',
                'done'
            ])->default('pending')->index();
            $table->unsignedBigInteger('last_id')->default(0)->index();
            $table->unsignedBigInteger('total')->default(0);
            $table->string('database_host');
            $table->integer('port')->default(3306);
            $table->string('database_name');
            $table->string('user_name');
            $table->string('password');
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
        Schema::dropIfExists('sync_old_users');
    }
};
