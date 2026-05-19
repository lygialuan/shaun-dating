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
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->index();
            $table->string('subject_type', 128);
            $table->bigInteger('subject_id');
            $table->text('content');
            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->bigInteger('like_count')->default(0);
            $table->bigInteger('reply_count')->default(0);
            $table->string('mentions', 512)->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamps();
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
        Schema::dropIfExists('comments');
    }
};
