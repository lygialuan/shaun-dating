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
        Schema::create('layout_blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('component')->unique()->index();
            $table->string('title')->index();
            $table->string('package', 32)->default('shaun_core');
            $table->string('class')->nullable();
            $table->boolean('support_header_footer')->default(false);
            $table->boolean('enable')->default(true);
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
        Schema::dropIfExists('layout_blocks');
    }
};