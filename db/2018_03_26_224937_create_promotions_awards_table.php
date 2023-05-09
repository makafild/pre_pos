<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions_awards', function (Blueprint $table) {

			$table->integer('promotions_id')->unsigned();
			$table->foreign('promotions_id')->references('id')->on('promotions');

			$table->integer('product_id')->unsigned();
			$table->foreign('product_id')->references('id')->on('products');

			$table->integer('master')->default(0);
			$table->integer('slave')->default(0);
			$table->integer('slave2')->default(0)->nullable();

			$table->integer('discount')->default(100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions_basket');
    }
}
