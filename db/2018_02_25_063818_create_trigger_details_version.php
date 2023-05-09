<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerDetailsVersion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::unprepared("

CREATE TRIGGER update_details BEFORE UPDATE ON `details`
FOR EACH ROW BEGIN
              INSERT INTO details_version
          (
            id,master,slave2,total,per_master,per_slave,
            master_unit_id,slave_unit_id, product_id,
            unit_price,price,final_price,markup_price,
            discount,prise,promotions_id, order_id,

            created_at,
            updated_at,
            version
          )

         values
         (
            OLD.id,OLD.master,OLD.slave2,OLD.total,OLD.per_master,OLD.per_slave,
            OLD.master_unit_id,OLD.slave_unit_id,OLD.product_id,
            OLD.unit_price,OLD.price,OLD.final_price,OLD.markup_price,
            OLD.discount,OLD.prise,OLD.promotions_id,OLD.order_id,

            OLD.created_at,
            OLD.updated_at,
            OLD.version
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
        \Illuminate\Support\Facades\DB::unprepared("DROP trigger_details_version");
    }
}
