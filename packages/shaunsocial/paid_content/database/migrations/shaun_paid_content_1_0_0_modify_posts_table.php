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
            $table->bigInteger('thumb_file_id')->default(0);
            $table->boolean('is_paid')->default(0)->index();
            $table->double('content_amount')->default(0);
            $table->double('earn_amount')->default(0);
            $table->enum('paid_type', [
                'subscriber',
                'pay_per_view'
            ])->default('subscriber');
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