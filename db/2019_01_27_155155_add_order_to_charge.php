<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderToCharge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('charges', function (Blueprint $table) {
			$table->integer('order_id')->unsigned()->nullable();
			$table->foreign('order_id')->references('id')->on('orders')->delete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('charges', function (Blueprint $table) {
			$table->dropForeign('order_id');
			$table->dropColumn('order_id');
		});
    }
}
