<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSliderRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slider_route', function (Blueprint $table) {
        // slider
			$table->integer('slider_id');

			// area
			$table->integer('route_id');
            $table->primary(['slider_id', 'route_id']);

			//$table->foreign('areas_id')->references('id')->on('areas');
            //$table->foreign('slider_id')->references('id')->on('sliders')
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slider_route');
    }
}
