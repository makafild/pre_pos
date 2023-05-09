<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class CouponCustomer extends Model
{
	protected $fillable = [
		'coupon_id', 'user_id',
	];

	public $timestamps = false;
}
