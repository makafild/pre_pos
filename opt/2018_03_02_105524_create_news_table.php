<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('news', function (Blueprint $table) {
			$table->increments('id');

			$table->string('title');
			$table->text('description');

			$table->integer('photo_id')->unsigned();
			$table->foreign('photo_id')->references('id')->on('files');

			$table->integer('company_id')->unsigned()->nullable();
			$table->foreign('company_id')->references('id')->on('users');

			$table->integer('creator_id')->unsigned();
			$table->foreign('creator_id')->references('id')->on('users');

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
		Schema::dropIfExists('news');
	}
}
