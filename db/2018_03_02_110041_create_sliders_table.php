<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlidersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sliders', function (Blueprint $table) {
			$table->increments('id');

			$table->string('kind');

			$table->string('link')->nullable();

			$table->integer('company_id')->unsigned()->nullable();
			$table->foreign('company_id')->references('id')->on('users');

			$table->integer('product_id')->unsigned()->nullable();
			$table->foreign('product_id')->references('id')->on('products');

			$table->integer('file_id')->unsigned();
			$table->foreign('file_id')->references('id')->on('files');

			$table->string('status')->default('active');

			$table->integer('row_version')->default(0);
			$table->timestamp('start_at')->nullable();
			$table->timestamp('end_at')->nullable();
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

		Schema::dropIfExists('sliders');
	}
}
