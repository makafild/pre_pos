<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailsVersionTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('details_version', function (Blueprint $table) {
			$table->integer('id');

			$table->integer('master')->default(0);
			$table->integer('slave')->default(0);
			$table->integer('slave2')->default(0)->nullable();
			$table->integer('total')->default(0);

			$table->integer('per_master')->default(0);
			$table->integer('per_slave')->default(0);

			$table->integer('master_unit_id')->unsigned();
			$table->foreign('master_unit_id')->references('id')->on('constants');

			$table->integer('slave_unit_id')->unsigned();
			$table->foreign('slave_unit_id')->references('id')->on('constants');

			$table->integer('slave2_unit_id')->unsigned()->nullable();
			$table->foreign('slave2_unit_id')->references('id')->on('constants');

			$table->integer('unit_price')->default(0);
			$table->integer('price')->default(0);
			$table->integer('final_price')->default(0);
			$table->integer('markup_price')->default(0);
			$table->integer('discount')->default(0);
			$table->integer('version')->default(1);

			$table->boolean('prise')->default(false);

			$table->integer('promotions_id')->unsigned()->nullable();
			$table->foreign('promotions_id')->references('id')->on('promotions');


			// product
			$table->integer('product_id')->unsigned();
			$table->foreign('product_id')->references('id')->on('products');

			// order
			$table->integer('order_id')->unsigned();
			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

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
		Schema::dropIfExists('details_version');
	}
}
