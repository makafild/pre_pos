<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVisitTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visit_time', function (Blueprint $table) {
            //
            $table->dateTime('time_start')->nullable();
            $table->dateTime('time_end')->nullable();
            $table->text('time_visited')->nullable();
            $table->dropColumn('time_visit');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visit_time', function (Blueprint $table) {
            $table->dropColumn('time_start');
            $table->dropColumn('time_end');
            $table->dropColumn('time_visited')->nullable();
            $table->time('time_visit')->nullable();


            //
        });
    }
}
