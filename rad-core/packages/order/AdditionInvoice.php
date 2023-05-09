<?php

namespace App\Models\Order;

use App\Models\Setting\Constant;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Addition
 *
 * @package App\Models\Order
 * @property int      $id
 *
 * @property int      $order_id
 * @property Order    $order
 *
 * @property int      $key_id
 * @property Constant $key
 *
 * @property int      $value
 */
class AdditionInvoice extends Model
{
    protected $fillable = [
        'order_invoice_id',
        'key_id',
        'value',
        'created_at',
        'updated_at'
    ];
	public function Order()
	{
		return $this->belongsTo(Order::class);
	}

	public function Key()
	{
		return $this->belongsTo(Constant::class);
	}
}
