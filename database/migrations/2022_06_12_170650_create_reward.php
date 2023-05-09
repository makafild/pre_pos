<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward', function (Blueprint $table) {
            $table->bigIncrements('id');//شناسه
            $table->integer('promotion_id');//شناسه ی پروموشن
            $table->integer('coefficient')->nullable();//ضریب
            $table->string('basket_kind')->nullable();//ضریب
            $table->string('function_coefficient')->nullable();//نحوه ی محاسبه ی پله ای total|stepByStep|onlyRow
            $table->string('operating');//عملکرد :remain_price|main_price|not
            $table->integer('mini_row')->nullable();//حدالقل سطر
            $table->integer('max_row')->nullable();//حدلکثر سطر
            $table->integer('min_price_factor')->nullable();//حدالقل مبلغ فاکتور
            $table->integer('max_price_factor')->nullable();//حدالکثر مبلغ فاکتور
            $table->integer('sequence');//ترتیب یا الویت در اجرا
            $table->integer('discount_precent')->nullable();//درصد تخفیف
            $table->integer('discount_money')->nullable();//درصد ریالی
            $table->string('discount_function');//اعمال تخفیف وی محصول یا فاکتور:product|factor
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
        Schema::dropIfExists('reward');
    }
}
