<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogApiCrmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_api_crm', function (Blueprint $table) {
            $table->increments('id');

            $table->string('mobile')->nullable();
			$table->string('phone')->nullable();
			$table->text('input')->nullable();
			$table->text('output')->nullable();
			$table->string('referral_id')->nullable();
			$table->string('status')->nullable();
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
        Schema::dropIfExists('log_api_crm');
    }
}
