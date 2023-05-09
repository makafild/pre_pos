<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyReportsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_reports', function (Blueprint $table) {
			$table->increments('id');

			$table->text('turn_overs')->nullable();
			$table->text('account_balances')->nullable();
			$table->text('factors')->nullable();
			$table->text('return_cheques')->nullable();

			$table->integer('customer_id')->unsigned();
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
		Schema::dropIfExists('company_reports');
	}
}
