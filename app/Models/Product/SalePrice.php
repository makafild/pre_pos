<?php

namespace App\Models\Product;

use App\Models\Setting\Constant;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SalePrice
 *
 * @package App\Models\Product
 * @property int      $id
 * @property int      $price
 *
 * @property int      $customer_category_id
 * @property Constant $customer_category
 *
 * @property int      $product_id
 * @property Product  $product
 */
class SalePrice extends Model
{
	public function CustomerCategory()
	{
		return $this->belongsTo(Constant::class);
	}

	public function Product()
	{
		return $this->belongsTo(Product::class)->withTrashed();
	}
}
