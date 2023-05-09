<?php

namespace App\Models\Common;

use App\Models\Product\Product;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Favorite
 *
 * @package App\Models\Common
 * @property integer $customer_id
 * @property integer $product_id
 */
class Favorite extends Model
{
	protected $fillable = [
		'customer_id',
		'product_id',
	];

	protected $primaryKey = 'product_id';

	public $incrementing = false;

	public $timestamps = false;

	public function Customer()
	{
		$this->belongsTo(User::class, 'customer_id')->withTrashed();
	}

	public function Product()
	{
		$this->belongsTo(Product::class)->withTrashed();
	}

	public function scopeMine($query)
	{
		return $query->where('customer_id', auth()->id());
	}
}
