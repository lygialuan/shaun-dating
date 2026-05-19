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
        Schema::create('setting_group_subs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('group_id')->index();
            $table->string('key')->unique()->index();
            $table->integer('order')->default('1');
            $table->string('package', 32)->default('shaun_core');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting_group_subs');
    }
};
