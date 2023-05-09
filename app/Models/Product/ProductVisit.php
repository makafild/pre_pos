<?php

namespace App\Models\Product;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Score
 *
 * @package App\Models\Product
 * @property int $score
 */
class ProductVisit extends Model
{

	protected $fillable = [
		'user_id',
		'product_id'
	];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
