<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function (Blueprint $table) {
			$table->increments('id');

			$table->integer('price_without_promotions')->default(0);
			$table->integer('promotion_price')->default(0);
			$table->integer('price_with_promotions')->default(0);
			$table->integer('amount_promotion')->default(0);
			$table->integer('discount')->default(0);
			$table->integer('final_price')->default(0);
			$table->integer('markup_price')->default(0);

			// customer
			$table->integer('customer_id')->unsigned();
			$table->foreign('customer_id')->references('id')->on('users');

            $table->integer('visitor_id')->unsigned();
            $table->foreign('visitor_id')->references('id')->on('users');

			// company
			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

			// company
			$table->integer('payment_method_id')->unsigned()->nullable();
			$table->foreign('payment_method_id')->references('id')->on('constants');

			// company
			$table->integer('coupon_id')->unsigned()->nullable();
			$table->foreign('coupon_id')->references('id')->on('coupons');

			$table->text('tracker_url')->nullable();
			$table->text('factor_id')->nullable();
			$table->text('referral_id')->nullable();

			$table->string('status')->default('registered');
            $table->integer('version')->default(1);

			$table->integer('row_version')->default(0);
            $table->integer('updated_by')->nullable();
            $table->date('date_of_sending')->nullable();
			$table->string('registered');
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
		Schema::dropIfExists('orders');
	}
}
