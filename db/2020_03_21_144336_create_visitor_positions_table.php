<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitorPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visitor_positions', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->foreign('user_id')->references('id')->on('users');
			$table->string('accessibility');
			$table->string('device_id');
			$table->string('accuracy')->nullable();
			$table->string('altitude')->nullable();
			$table->string('heading')->nullable();
			$table->string('latitude')->nullable();
			$table->string('longitude')->nullable();
			$table->string('timestamp')->nullable();
			$table->string('speed')->nullable();
			$table->string('timeout')->nullable();
			$table->string('position_unavailable')->nullable();
			$table->string('permission_denied')->nullable();
			$table->mediumText('message')->nullable();
			$table->string('code')->nullable();
			$table->string('location_status');
			$table->string('network');
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
        Schema::dropIfExists('positions');
    }
}
