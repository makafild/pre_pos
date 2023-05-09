<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id');
            $table->integer('stockroom_id');
            $table->integer('order_id');
            $table->bigInteger('Series');
            $table->integer(('price_index'));
            $table->integer('master')->nullable();
            $table->integer('slave')->nullable();
            $table->integer('slave2')->nullable();
            $table->integer('count')->nullable();
            $table->integer('count_s')->nullable();
            $table->integer('count_s2')->nullable();
            $table->date('expier');
            $table->integer('discount')->nullable();
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
        Schema::dropIfExists('receipts_orders');
    }
}
