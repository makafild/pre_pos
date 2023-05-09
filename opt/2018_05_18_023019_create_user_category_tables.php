<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCategoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_category', function (Blueprint $table) {
			// user
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');

			// city
			$table->integer('constant_id')->unsigned();
			$table->foreign('constant_id')->references('id')->on('constants');

		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_category');
    }
}
