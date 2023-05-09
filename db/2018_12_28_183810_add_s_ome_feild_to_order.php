<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSOmeFeildToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('description')->nullable()->default('');
            $table->enum('payment_confirm', \App\Models\Order\Order::PAYMENTS)->default(\App\Models\Order\Order::PAYMENT_DEFAULT);
            $table->string('transfer_number')->nullable()->default('');
            $table->string('carriage_fares')->nullable()->default('');

			// company
			$table->integer('new_payment_method_id')->unsigned()->nullable();
			$table->foreign('new_payment_method_id')->references('id')->on('payment_methods');

		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
