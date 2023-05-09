<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');

            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();

            // user
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
			$table->softDeletes();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
