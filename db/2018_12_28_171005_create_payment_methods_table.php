<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_methods', function (Blueprint $table) {
			$table->increments('id');

			$table->integer('payment_method_id')->unsigned();
			$table->foreign('payment_method_id')->references('id')->on('constants');

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

			$table->string('discount');
			$table->string('discount_max');

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
		Schema::dropIfExists('payment_methods');
	}
}
