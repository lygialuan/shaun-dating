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
        Schema::create('user_subscription_package_compare_columns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('package_id')->index('uspc_package_id_idx');
            $table->integer('compare_id')->index();
            $table->enum('type', [
                'text',
                'boolean',
            ])->default('text');
            $table->string('value')->nullable();
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
        Schema::dropIfExists('user_subscription_package_compare_columns');
    }
};
