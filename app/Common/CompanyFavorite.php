<?php

namespace App\Common;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Favorite
 *
 * @package App\Models\Common
 * @property integer $customer_id
 * @property integer $company_id
 */
class CompanyFavorite extends Model
{
	protected $fillable = [
		'customer_id',
		'company_id',
	];

	protected $primaryKey = 'company_id';

	public $incrementing = false;

	public $timestamps = false;

	public function Customer()
	{
		$this->belongsTo(User::class, 'customer_id');
	}

	public function Company()
	{
		$this->belongsTo(User::class, 'company_id');
	}

	public function scopeMine($query)
	{
		return $query->where('customer_id', auth()->id());
	}
}
