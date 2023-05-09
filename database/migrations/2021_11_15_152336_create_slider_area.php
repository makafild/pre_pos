<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSliderArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slider_area', function (Blueprint $table) {
           // slider
			$table->integer('slider_id');

			// area
			$table->integer('areas_id');
            $table->primary(['slider_id', 'areas_id']);

			//$table->foreign('areas_id')->references('id')->on('areas');
            //$table->foreign('slider_id')->references('id')->on('sliders');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slider_area');
    }
}
