<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockroomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stockrooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->enum('type' , ['reserve' , 'sale', 'wastage']);
            $table->text('address');
            $table->integer('company_id');
            $table->integer('keeper');
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
        Schema::dropIfExists('stockrooms');
    }
}
