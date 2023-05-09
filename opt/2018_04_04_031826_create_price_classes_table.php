<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceClassesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('price_classes', function (Blueprint $table) {
			$table->increments('id');

			$table->string('title');
			$table->string('referral_id')->nullable();

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

			$table->timestamps();
		});

		Schema::create('price_class_product', function (Blueprint $table) {

			$table->string('price');

			$table->integer('product_id')->unsigned();
			$table->foreign('product_id')->references('id')->on('products');

			$table->integer('price_class_id')->unsigned();
			$table->foreign('price_class_id')->references('id')->on('price_classes');

		});

		Schema::create('price_class_customer', function (Blueprint $table) {

			$table->integer('customer_id')->unsigned();
			$table->foreign('customer_id')->references('id')->on('users');

			$table->integer('price_class_id')->unsigned();
			$table->foreign('price_class_id')->references('id')->on('price_classes');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('price_class_customer');
		Schema::dropIfExists('price_class_product');
		Schema::dropIfExists('price_classes');
	}
}
