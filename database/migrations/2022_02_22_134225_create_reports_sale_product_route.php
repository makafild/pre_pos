<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsSaleProductRoute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_sale_product_route', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->biginteger('route_id');
            $table->biginteger('product_id');
            $table->biginteger('num_facktor');
            $table->biginteger('num_master_sale');
            $table->biginteger('num_slave_sale');
            $table->biginteger('num_slave2_sale');
            $table->biginteger('sum_num_slave2_sale');
            $table->biginteger('price_num_slave2_sale');
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
        Schema::dropIfExists('reports_sale_product_route');
    }
}
