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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('role_id')->default(0)->index();
            $table->string('user_name', 128)->unique()->index();
            $table->bigInteger('avatar_file_id')->default(0);
            $table->bigInteger('follower_count')->default(0);
            $table->bigInteger('following_count')->default(0);
            $table->bigInteger('hashtag_follow_count')->default(0);
            $table->bigInteger('block_count')->default(0);
            $table->bigInteger('notify_count')->default(0);
            $table->string('bio')->nullable();
            $table->text('about')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->string('location')->nullable();
            $table->bigInteger('gender_id')->default(0);
            $table->boolean('enable_notify')->default(true);
            $table->text('notify_setting')->nullable();
            $table->bigInteger('cover_file_id')->default(0);
            $table->integer('privacy')->default(1);
            $table->text('privacy_setting')->nullable();
            $table->string('ref_code',16)->index();
            $table->boolean('already_setup_login')->default(0);
            $table->boolean('daily_email_enable')->default(1);
            $table->text('email_setting')->nullable();            
            $table->enum('darkmode',['auto', 'on', 'off'])->default('auto');
            $table->string('timezone')->default('Europe/London');
            $table->boolean('is_active')->default(true);
            $table->boolean('has_active')->default(false)->index();
            $table->date('birthday')->nullable();
            $table->text('links')->nullable();
            $table->text('hashtags')->nullable()->fulltext();
            $table->string('ip')->nullable()->index();
            $table->string('language',16)->default('en');
            $table->boolean('video_auto_play')->default(true);
            $table->boolean('has_email')->default(true);

            $table->index(['privacy', 'is_active', 'follower_count'], 'privacy_follower_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('role_id');
            $table->dropColumn('user_name');
            $table->dropColumn('avatar_file_id');
            $table->dropColumn('follower_count');
            $table->dropColumn('following_count');
            $table->dropColumn('block_count');
            $table->dropColumn('notify_count');
            $table->dropColumn('hashtag_follow_count');
            $table->dropColumn('bio');
            $table->dropColumn('about');
            $table->dropColumn('email_verified');
            $table->dropColumn('location');
            $table->dropColumn('gender_id');
            $table->dropColumn('enable_notify');
            $table->dropColumn('notify_setting');
            $table->dropColumn('cover_file_id');
            $table->dropColumn('privacy');
            $table->dropColumn('privacy_setting');
            $table->dropColumn('ref_code');
            $table->dropColumn('already_setup_login');
            $table->dropColumn('daily_email_enable');
            $table->dropColumn('email_setting');
            $table->dropColumn('darkmode');
            $table->dropColumn('is_active');
            $table->dropColumn('has_active');
            $table->dropColumn('birthday');
            $table->dropColumn('links');
            $table->dropColumn('hashtags');
            $table->dropColumn('language');
            $table->dropColumn('video_auto_play');
            $table->dropColumn('has_email');
        });
    }
};
