<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntroducerCodesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('introducer_codes', function (Blueprint $table) {
			$table->increments('id');

			$table->string('code')->unique();
			$table->string('title');

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

			$table->string('status');

			$table->timestamps();
			$table->softDeletes();
		});

		Schema::table('users', function (Blueprint $table) {
			$table->foreign('introducer_code_id')->references('id')->on('introducer_codes');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('introducer_codes');
	}
}
