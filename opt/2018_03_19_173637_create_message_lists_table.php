<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_lists', function (Blueprint $table) {
        	$table->increments('id');
        	$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
        	$table->integer('to_id')->unsigned();
			$table->foreign('to_id')->references('id')->on('users');
			$table->text('message');
            $table->timestamp('seen_at')->nullable();
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
        Schema::dropIfExists('message_lists');
    }
}
