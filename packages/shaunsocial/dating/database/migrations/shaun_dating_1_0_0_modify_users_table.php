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
            $table->boolean('photos_verified')->default(false);
            $table->boolean('has_reviewed_photos')->default(true);
            $table->timestamp('photos_uploaded_at')->nullable();
            $table->boolean('identity_verified')->default(false);
            $table->string('school_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->string('attributes', 255)->nullable()->fulltext();
            $table->string('interest_attributes', 255)->nullable()->fulltext();
            $table->text('dating_addresses_fulltext')->nullable()->fulltext();
            $table->unsignedInteger('dating_addresses_id')->index()->default(0);
            $table->bigInteger('blur_avatar_file_id')->default(0);
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
            $table->dropColumn('photos_verified');
            $table->dropColumn('has_reviewed_photos');
            $table->dropColumn('photos_uploaded_at');
            $table->dropColumn('identity_verified');
            $table->dropColumn('school_name');
            $table->dropColumn('job_title');
            $table->dropColumn('company_name');
            $table->dropColumn('attributes');
            $table->dropColumn('interest_attributes');
            $table->dropColumn('dating_addresses_fulltext');
            $table->dropColumn('dating_addresses_id');
            $table->dropColumn('blur_avatar_file_id');
        });
    }
};
