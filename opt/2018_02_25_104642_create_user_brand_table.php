<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBrandTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_brand', function (Blueprint $table) {
			// user
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');

			// city
			$table->integer('brand_id')->unsigned();
			$table->foreign('brand_id')->references('id')->on('brands');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('user_brand');
	}
}
