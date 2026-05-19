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
        Schema::create('advertisings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index();
            $table->bigInteger('user_id')->index();
            $table->double('sort_count')->default(0)->index();
            $table->bigInteger('post_id')->index();
            $table->integer('gender_id')->default(0)->index();
            $table->enum('age_type',[
                'any',
                'range'
            ])->default('any');
            $table->integer('age_from')->default(0)->index();
            $table->integer('age_to')->default(0)->index();
            $table->string('hashtags', 1028)->nullable()->fulltext();
            $table->double('daily_amount')->default(0);
            $table->double('vat')->default(0);
            $table->double('total_delivery_amount')->default(0);
            $table->boolean('check_done')->default(false)->index();
            $table->string('currency');
            $table->string('timezone');
            $table->double('view_count')->default(0);
            $table->double('click_count')->default(0);
            $table->double('amount_per_click')->default(0);
            $table->double('amount_per_view')->default(0);
            $table->dateTime('start')->index();
            $table->dateTime('end')->index();
            $table->integer('date_stop')->default(0)->index();
            $table->enum('status', [
                'active',
                'stop',
                'done'
            ])->default('active');
            $table->boolean('notify_sent')->default(false)->index();
            $table->integer('country_id')->default(0)->index();
            $table->integer('state_id')->default(0)->index();
            $table->integer('city_id')->default(0)->index();
            $table->string('zip_code', 32)->nullable()->index();
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
        Schema::dropIfExists('advertisings');
    }
};
