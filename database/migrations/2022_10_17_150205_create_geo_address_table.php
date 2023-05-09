<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geo_address', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('address');
            $table->integer('area_id')->nullable();
            $table->integer('city_id');
            $table->integer('user_id');
            $table->string('postal_code' , 10);
            $table->string('lat' , 100);
            $table->string('long' , 100);
            $table->integer('receiver_id');
            $table->enum('default' , ['0' , '1'])->default('0');
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
        Schema::dropIfExists('geo_address');
    }
}
