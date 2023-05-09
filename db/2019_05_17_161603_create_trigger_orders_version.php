<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerOrdersVersion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::unprepared("

CREATE TRIGGER update_orders BEFORE UPDATE ON `orders`
FOR EACH ROW BEGIN
       INSERT INTO orders_version
          (
            id,price_without_promotions,promotion_price,
            price_with_promotions,amount_promotion,discount,
            final_price,markup_price,visitor_id,customer_id,
            company_id,order_id,payment_method_id,coupon_id,
            tracker_url,factor_id,referral_id,status,row_version,
            date_of_sending,created_at,updated_at,deleted_at,
            description,payment_confirm,transfer_number,carriage_fares,
            new_payment_method_id,imei,registered,registered_by,updated_by,version
          )

         values
         (
            OLD.id,OLD.price_without_promotions,OLD.promotion_price,
            OLD.price_with_promotions,OLD.amount_promotion,OLD.discount,
            OLD.final_price,OLD.markup_price,OLD.visitor_id,OLD.customer_id,
            OLD.company_id,OLD.order_id,OLD.payment_method_id,OLD.coupon_id,
            OLD.tracker_url,OLD.factor_id,OLD.referral_id,OLD.status,OLD.row_version,
            OLD.date_of_sending,OLD.created_at,OLD.updated_at,OLD.deleted_at,
            OLD.description,OLD.payment_confirm,OLD.transfer_number,OLD.carriage_fares,
            OLD.new_payment_method_id,OLD.imei,OLD.registered,OLD.registered_by,updated_by,version
         );
         SET NEW.version = OLD.version+1;

         END;

        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::unprepared("DROP trigger_orders_version");
    }
}
