<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTabelProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->integer('min_quotas_master')->default(0);
            $table->integer('min_quotas_slave')->default(0);
            $table->integer('min_quotas_slave2')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn('min_quotas_master');
            $table->dropColumn('min_quotas_slave');
            $table->dropColumn('min_quotas_slave2');
        });
    }
}
