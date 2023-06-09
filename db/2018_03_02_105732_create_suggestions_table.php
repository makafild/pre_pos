<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuggestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->increments('id');

            $table->string('suggestion');

			$table->integer('company_id')->unsigned()->nullable();
			$table->foreign('company_id')->references('id')->on('users');

			$table->integer('user_id')->unsigned()->nullable();
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
        Schema::dropIfExists('suggestions');
    }
}
