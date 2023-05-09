<?php

namespace App\Models\Order;

use App\Models\Product\Product;
use App\Models\Product\Promotions;
use App\Models\Setting\Constant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Detail
 *
 * @package App\Models\Order
 * @property int        $id
 *
 * @property int        $master
 * @property int        $slave
 * @property int        $slave2
 *
 * @property int        $per_master
 * @property int        $per_slave
 * @property int        $per_slave2
 *
 * @property int        $total
 *
 * @property int        $master_unit_id
 * @property Constant   $master_unit
 *
 * @property int        $slave_unit_id
 * @property Constant   $slave_unit
 *
 * @property int        $slave2_unit_id
 * @property Constant   $slave2_unit
 *
 * @property int        $unit_price
 * @property int        $price
 * @property int        $discount
 * @property int        $final_price
 * @property boolean    $prise
 *
 * @property int        $promotions_id
 * @property Promotions $promotions
 *
 * @property int        $product_id
 * @property Product    $product
 *
 * @property int        $order_id
 * @property Order      $order
 *
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 */
class Detail extends Model
{
	protected $casts = [
		'prise' => 'boolean',
	];

	// ********************************* Relations *********************************

	public function Order()
	{
		return $this->belongsTo(Order::class);
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
