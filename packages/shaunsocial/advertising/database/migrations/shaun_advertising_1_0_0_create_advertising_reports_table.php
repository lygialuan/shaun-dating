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
        Schema::create('advertising_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('advertising_id')->index();
            $table->double('view_count')->default(0);
            $table->double('click_count')->default(0);
            $table->date('date');
            $table->enum('status', [
                'process',
                'done',
                'stop'
            ])->default('process')->index();
            $table->double('view_amount')->default(0);
            $table->double('click_amount')->default(0);
            $table->double('total_amount')->default(0);
            $table->boolean('check_done')->default(false)->index();
            $table->string('currency');
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
        Schema::dropIfExists('advertising_reports');
    }
};
