<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('coupons', function (Blueprint $table) {
			$table->increments('id');
			$table->string('kind');
			$table->string('coupon');

			$table->integer('percentage')->nullable();
			$table->integer('amount')->nullable();

			$table->integer('company_id')->unsigned()->nullable();
			$table->foreign('company_id')->references('id')->on('users');

			$table->integer('row_version')->default(0);
			$table->string('status')->default('active');
			$table->timestamps();
			$table->softDeletes();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('coupons');
	}
}
