<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodicDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periodic_details', function (Blueprint $table) {
			$table->increments('id');

			$table->integer('master')->default(0);
			$table->integer('slave')->default(0);
			$table->integer('slave2')->default(0)->nullable();
			$table->integer('total')->default(0);

			$table->integer('master_unit_id')->unsigned();
			$table->foreign('master_unit_id')->references('id')->on('constants');

			$table->integer('slave_unit_id')->unsigned();
			$table->foreign('slave_unit_id')->references('id')->on('constants');

			$table->integer('slave2_unit_id')->unsigned()->nullable();
			$table->foreign('slave2_unit_id')->references('id')->on('constants');

			// product
			$table->integer('product_id')->unsigned();
			$table->foreign('product_id')->references('id')->on('products');

			// order
			$table->integer('order_id')->unsigned();
			$table->foreign('order_id')->references('id')->on('periodic_orders');

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
        Schema::dropIfExists('periodic_details');
    }
}
