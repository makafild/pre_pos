<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCustomersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_customers', function (Blueprint $table) {
			$table->increments('id');

			$table->string('referral_id');
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();

			$table->string('national_id')->nullable();
			$table->string('economic_code')->nullable();

			$table->string('email')->nullable();
			$table->string('mobile_number')->nullable();

			$table->text('address')->nullable();

			$table->integer('price_class_id')->nullable();

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

			$table->integer('customer_id')->unsigned()->nullable();
			$table->foreign('customer_id')->references('id')->on('users');

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
		Schema::dropIfExists('company_customers');
	}
}
