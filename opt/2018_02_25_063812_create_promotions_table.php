<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('promotions', function (Blueprint $table) {
			$table->increments('id');

			$table->string('kind');
			$table->string('title');
			$table->text('description');

			$table->string('discount')->nullable();
			$table->string('amount')->nullable();

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');


			$table->integer('row_version')->default(0);
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
		Schema::dropIfExists('promotions');
	}
}
