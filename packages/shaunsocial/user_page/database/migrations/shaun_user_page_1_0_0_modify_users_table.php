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
            $table->boolean('is_page')->default(false)->index();
            $table->text('categories')->nullable()->fulltext();
            $table->text('page_hashtags')->nullable()->fulltext();
            $table->text('page_info_privacy')->nullable();
            $table->boolean('is_page_feature')->default(false)->index();
            $table->double('page_feature_view')->default(0)->index();
            $table->boolean('fake_user')->default(0)->index();
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
            $table->dropColumn('is_page');
            $table->dropColumn('categories');
            $table->dropColumn('page_hashtags');
            $table->dropColumn('page_info_privacy');
            $table->dropColumn('fake_user');
        });
    }
};
