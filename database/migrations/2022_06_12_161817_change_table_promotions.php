<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTablePromotions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('promotions', function (Blueprint $table) {
            //
            $table->dropColumn('discount');
            $table->dropColumn('amount');
            $table->dropColumn('row_version');
            $table->dropColumn('end_at');
            $table->dropColumn('activation_at');
            $table->dropColumn('volumes');
            $table->dropColumn('row_product_status');
            $table->dropColumn('basket_kind');
            $table->integer('repeat_total')->nullable();//چند بار قابل استفاده
            $table->integer('repeat_for_customer')->nullable();//چند بار قابل استفاده برای مشتری
            $table->integer('count_use')->nullable()->default('0');//چند بار استفاده شده
            $table->integer('sequence');//ترتیب یا اولویت اجرا
            $table->string('operating');//عملکرد :remain_price|main_price|not
            $table->json('provinces')->nullable();
            $table->json('city')->nullable();
            $table->json('area')->nullable();
            $table->json('route')->nullable();
            $table->json('price_classes')->nullable();
            $table->json('customers')->nullable();
            $table->json('introduction_source')->nullable();
            $table->json('customer_group')->nullable();
            $table->json('payment_method')->nullable();



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('promotions', function (Blueprint $table) {
            //
        $table->string('discount');
        $table->string('amount');
        $table->string('row_version');
        $table->string('end_at');
        $table->string('basket_kind');
        $table->string('activation_at');
        $table->string('volumes');
        $table->string('row_product_status');
        $table->dropColumn('repeat_total');
        $table->dropColumn('repeat_for_customer');
        $table->dropColumn('count_use');
        $table->dropColumn('sequence');
        $table->dropColumn('provinces');
        $table->dropColumn('city');
        $table->dropColumn('area');
        $table->dropColumn('route');
        $table->dropColumn('price_classes');
        $table->dropColumn('customers');
        $table->dropColumn('introduction_source');
        $table->dropColumn('customer_group');
        $table->dropColumn('payment_method');
        $table->dropColumn('operating');
        });
    }

}
