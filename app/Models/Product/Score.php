<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Score
 *
 * @package App\Models\Product
 * @property int $score
 */
class Score extends Model
{
	protected $table = "product_scores";

	protected $fillable = [
		'user_id',
		'product_id',
	];
}
