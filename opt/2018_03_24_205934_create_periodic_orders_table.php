<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodicOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periodic_orders', function (Blueprint $table) {
			$table->increments('id');

			// customer
			$table->integer('customer_id')->unsigned();
			$table->foreign('customer_id')->references('id')->on('users');

			// company
			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

			$table->string('days');

			$table->string('status')->default('registered');

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
        Schema::dropIfExists('periodic_orders');
    }
}
