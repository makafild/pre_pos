<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('reward_id');
            $table->integer('num_master')->nullable();
            $table->integer('num_slave')->nullable();
            $table->integer('num_slave2')->nullable();
            $table->integer('product_id');
            $table->integer('company_id');
            $table->integer('discount_precent')->nullable();
            $table->integer('discount_money')->nullable();
            $table->string('status_discount');
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
        Schema::dropIfExists('reward_product');
    }
}
