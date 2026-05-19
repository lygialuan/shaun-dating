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
        Schema::create('layout_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id')->index();
            $table->enum('position',[
                'top',
                'center',
                'right',
                'bottom',
            ]);
            $table->enum('view_type',[
                'desktop',
                'mobile',
                'header',
                'footer'
            ])->nullable();
            $table->enum('type',[
                'component',
                'container'
            ]);
            $table->string('title');
            $table->text('content')->nullable()->default(null);
            $table->boolean('enable_title')->default(false);
            $table->string('class')->nullable();
            $table->string('component')->nullable();
            $table->string('package', 32)->default('shaun_core');
            $table->string('role_access')->nullable();
            $table->text('params')->nullable()->default(null);
            $table->integer('order');

            $table->index(['page_id', 'view_type'], 'page_view_type');
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
        Schema::dropIfExists('layout_contents');
    }
};