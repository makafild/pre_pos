<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnvisitedReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unvisited_report', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('visitor_id')->unsigned();
            $table->foreign('visitor_id')->references('id')->on('users');

            $table->integer('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('users');

            $table->unique(['visitor_id','customer_id']);

            $table->string('status');
			$table->integer('unvisited_description_id')->nullable();
			$table->text('description')->nullable();
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
        Schema::dropIfExists('unvisited_report');
    }
}
