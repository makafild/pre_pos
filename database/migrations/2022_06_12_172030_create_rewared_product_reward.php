<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewaredProductReward  extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rewared_product_rewared', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('reward_id');
            $table->integer('discount_precent')->nullable();
            $table->integer('discount_money')->nullable();
            $table->integer('product_id');
            $table->integer('company_id');
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
        Schema::dropIfExists('rewared_product_rewared');
    }
}
