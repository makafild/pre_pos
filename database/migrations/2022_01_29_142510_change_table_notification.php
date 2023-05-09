<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
            $table->dropColumn('categories');
            $table->dropColumn('countries');
            $table->dropColumn('provinces');
            $table->dropColumn('cities');
            $table->string('Kind')->nullable();
            $table->integer('product')->nullable();
            $table->integer('company')->nullable();
            $table->integer('customer_typed')->nullable();
            $table->integer('sms')->nullable();
            $table->date('date_start')->nullable();
            $table->time('time_start')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
            $table->integer('categories');
            $table->integer('countries');
            $table->integer('provinces');
            $table->integer('cities');
            $table->dropColumn('Kind');
        });
    }
}
