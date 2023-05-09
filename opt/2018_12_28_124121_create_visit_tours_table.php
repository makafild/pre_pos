<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitToursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visit_tours', function (Blueprint $table) {
            $table->increments('id');

            $table->string('direction');
            $table->string('visitor');
            $table->string('visit_date');
            $table->string('visit_time');

			$table->integer('company_id')->unsigned();
			$table->foreign('company_id')->references('id')->on('users');

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
        Schema::dropIfExists('visit_tours');
    }
}
