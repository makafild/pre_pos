<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->increments('id');


			$table->string('amount');
			$table->string('payment');
			$table->string('method');
			$table->string('transaction_id')->nullable();
			$table->string('status');

			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');

//			$table->integer('coupon_id')->unsigned()->nullable();
//			$table->foreign('coupon_id')->references('id')->on('coupons');

			$table->integer('invoice_id')->unsigned();
			$table->foreign('invoice_id')->references('id')->on('invoices');

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
        Schema::dropIfExists('charges');
    }
}
