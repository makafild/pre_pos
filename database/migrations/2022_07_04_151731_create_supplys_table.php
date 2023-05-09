<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id');
            $table->integer('stockroom_id');
            $table->bigInteger('Series');
            $table->text('master');
            $table->text('slave')->nullable();
            $table->text('slave2')->nullable();
            $table->integer('count')->nullable();
            $table->integer('count_s')->nullable();
            $table->integer('count_s2')->nullable();
            $table->integer('master_sold')->nullable();
            $table->integer('slave_sold')->nullable();
            $table->integer('slave2_sold')->nullable();
            $table->date('expier');
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
        Schema::dropIfExists('supplys');
    }
}
