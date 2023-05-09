<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductUserCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
			$table->boolean('has_user_category')->default(false);

		});
        Schema::create('product_user_category', function (Blueprint $table) {
			// user
			$table->integer('product_id')->unsigned();
			$table->foreign('product_id')->references('id')->on('products');

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
        Schema::dropIfExists('product_user_category');
    }
}
