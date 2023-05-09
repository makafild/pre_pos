<?php

namespace Core\Packages\shop;


use Core\Packages\product\Product;
use Core\Packages\promotion\Promotions;
use Core\Packages\common\Constant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DetailInvoice extends Model
{
    protected $fillable = [
        'order_invoice_id',
        'master',
        'slave',
        'slave2',
        'total',
        'per_master',
        'per_slave',
        'master_unit_id',
        'slave_unit_id',
        'slave2_unit_id',
        'unit_price',
        'price',
        'final_price',
        'markup_price',
        'discount',
        'row_discount',
        'prise',
        'promotions_id',
        'product_id',
        'created_at',
        'updated_at',
        'version'
    ];

    protected $casts = [
        'prise' => 'boolean',
    ];

    // ********************************* Relations *********************************

    public function Order()
    {
        return $this->belongsTo(OrderInvoice::class);
    }

    public function Promotions()
    {
        return $this->belongsTo(Promotions::class)->withTrashed();
    }

    public function Product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }


    public function MasterUnit()
    {
        return $this->belongsTo(Constant::class);
    }

    public function SlaveUnit()
    {
        return $this->belongsTo(Constant::class);
    }

    public function Slave2Unit()
    {
        return $this->belongsTo(Constant::class);
    }
}
