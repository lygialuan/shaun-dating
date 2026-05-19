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
        Schema::create('user_download_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->index();
            $table->string('type',32)->index();
            $table->enum('status',[
                'running',
                'done'
            ])->default('running')->index();
            $table->longText('params')->nullable();
            $table->bigInteger('id_min')->default(0);
            $table->bigInteger('parent_id')->default(0)->index();
            $table->string('package', 32)->default('shaun_core');
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
        Schema::dropIfExists('user_download_items');
    }
};
