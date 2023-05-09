<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSliderLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('slider_category', function (Blueprint $table) {
			// slider
			$table->integer('slider_id')->unsigned();
			$table->foreign('slider_id')->references('id')->on('sliders');

			// city
			$table->integer('constant_id')->unsigned();
			$table->foreign('constant_id')->references('id')->on('constants');

		});

		Schema::create('slider_country', function (Blueprint $table) {

			// slider
			$table->integer('slider_id')->unsigned();
			$table->foreign('slider_id')->references('id')->on('sliders');

			// country
			$table->integer('country_id')->unsigned();
			$table->foreign('country_id')->references('id')->on('countries');

		});

		Schema::create('slider_province', function (Blueprint $table) {
			// slider
			$table->integer('slider_id')->unsigned();
			$table->foreign('slider_id')->references('id')->on('sliders');

			// province
			$table->integer('province_id')->unsigned();
			$table->foreign('province_id')->references('id')->on('provinces');

		});

		Schema::create('slider_city', function (Blueprint $table) {
			// slider
			$table->integer('slider_id')->unsigned();
			$table->foreign('slider_id')->references('id')->on('sliders');

			// city
			$table->integer('city_id')->unsigned();
			$table->foreign('city_id')->references('id')->on('cities');

		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('slider_category');
		Schema::dropIfExists('slider_country');
		Schema::dropIfExists('slider_province');
		Schema::dropIfExists('slider_city');
    }
}
