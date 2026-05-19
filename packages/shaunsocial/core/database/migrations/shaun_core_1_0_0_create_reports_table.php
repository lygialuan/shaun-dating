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
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('category_id');
            $table->string('reason',512)->nullable()->index();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
            $table->bigInteger('to_user_id')->default(0)->index();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'user_id'],'subject_user');     
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
        Schema::dropIfExists('reports');
    }
};
