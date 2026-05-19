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
        Schema::table('post_homes', function (Blueprint $table) {
            $table->string('source_type', 128)->default('');
            $table->bigInteger('source_id')->default(0);
            $table->integer('source_privacy')->default(0)->index();
            $table->index(['source_type', 'source_id'], 'source');
            $table->index(['source_type', 'source_id', 'source_privacy'], 'source_home');
            $table->boolean('has_source')->default(false)->index();
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
