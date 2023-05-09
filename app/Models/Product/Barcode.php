<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Barcode
 *
 * @package App\Models\Product
 * @property int    $id
 * @property string barcode
 */
class Barcode extends Model
{
	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
