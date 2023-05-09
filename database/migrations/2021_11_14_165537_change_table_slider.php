<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableSlider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sliders', function (Blueprint $table) {
            //



         /*   $table->Integer('provinces_id')->foreign()->references('id')->on('provinces');
            $table->Integer('city_id')->unsigned()->foreign()->references('id')->on('cities');
            $table->Integer('Area_id')->foreign()->references('id')->on('areas');
            $table->Integer('route_id')->unsigned()->foreign()->references('id')->on('routes');*/
            $table->dropColumn('row_version');


            //$table->foreign('provinces_id')->references('id')->on('provinces');
            // $table->foreign('city_id')->references('id')->on('cities');
            //$table->foreign('Area_id')->references('id')->on('areas');
            //$table->foreign('route_id')->references('id')->on('routes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sliders', function (Blueprint $table) {
            //
            //$table->dropForeign('provinces_id');
            // $table->dropForeign('city_id');
            // $table->dropForeign('Area_id');
            //  $table->dropForeign('route_id');
            $table->integer('row_version');
          /*  $table->dropColumn('provinces_id');
            $table->dropColumn('city_id');
            $table->dropColumn('Area_id');
            $table->dropColumn('route_id');*/
        });
    }
}
