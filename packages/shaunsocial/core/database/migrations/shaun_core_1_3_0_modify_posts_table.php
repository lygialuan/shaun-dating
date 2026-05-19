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
        Schema::table('posts', function (Blueprint $table) {
            $table->enum('comment_privacy',[
                'everyone',
                'following',
                'verified',
                'mentioned'
            ])->default('everyone');
            $table->string('content_warning_categories')->nullable();
            $table->double('view_count')->default(0);
			$table->string('source_type', 128)->default('');
            $table->bigInteger('source_id')->default(0);
            $table->integer('source_privacy')->default(0);
            $table->index(['source_type', 'source_id'], 'source');
            $table->boolean('has_source')->default(false)->index();
            $table->integer('pin_date')->default(0)->index();
            
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

    }
};