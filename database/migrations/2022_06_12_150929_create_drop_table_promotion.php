<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropTablePromotion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('promotions_route');
        Schema::dropIfExists('promotions_province');
        Schema::dropIfExists('promotions_price_class');
        Schema::dropIfExists('promotions_customer');
        Schema::dropIfExists('promotions_city');
        Schema::dropIfExists('promotions_category');
        Schema::dropIfExists('promotions_brand');
        Schema::dropIfExists('promotions_baskets');
        Schema::dropIfExists('promotions_awards');
        Schema::dropIfExists('promotions_area');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
