<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlaqueAndUnitInGeoAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('geo_address', function (Blueprint $table) {
            $table->integer('unit')->after('postal_code');
            $table->integer('plaque')->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('geo_address', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->dropColumn('plaque');
        });
    }
}
