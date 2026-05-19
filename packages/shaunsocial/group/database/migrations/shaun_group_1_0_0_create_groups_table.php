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
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->text('categories')->nullable()->fulltext();
            $table->text('hashtags')->nullable()->fulltext();
            $table->integer('member_count')->default(0);
            $table->bigInteger('cover_file_id')->default(0);
            $table->enum('who_can_post', [
                'member',
                'admin'
            ])->default('member');
            $table->string('slug');
            $table->integer('type')->default(0);
            $table->integer('member_request_count')->default(0);
            $table->integer('post_pending_count')->default(0);
            $table->integer('block_count')->default(0);
            $table->integer('admin_count')->default(0);
            $table->boolean('post_approve_enable')->default(false);
            $table->string('cache_key',16);
            $table->boolean('is_popular')->default(false);
            $table->enum('status', [
                'active',
                'hidden',
                'pending',
                'disable'
            ])->default('active')->index();
            $table->dateTime('datetime_change_status')->index();
            
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
        Schema::dropIfExists('groups');
    }
};
