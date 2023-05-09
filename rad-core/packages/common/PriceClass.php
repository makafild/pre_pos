<?php

namespace Core\Packages\common;


use Core\Packages\product\Product;
use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PriceClass
 *
 * @package App\Models\User
 * @property integer $id
 * @property integer $referral_id
 * @property string  $title
 *
 * @property int     $company_id
 * @property User    $Company
 *
 * @property User[]  $Customers
 *
 * @method static PriceClass ReferralId(int $referralId)
 */
class PriceClass extends Model
{
	public function Company()
	{
		return $this->belongsTo(Users::class)->withTrashed();
	}

	public function Customers()
	{
		return $this->belongsToMany(Users::class, 'price_class_customer', 'price_class_id', 'customer_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function Products()
	{
		return $this->belongsToMany(Product::class, 'price_class_product', 'price_class_id', 'product_id')
			->withPivot(['price']);
	}

	//

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId)
			return $query->where('company_id', $companyId);

		return $query;
	}

	public function scopeReferralId($query, $referralId)
	{
		return $query->where('referral_id', $referralId);
	}
}
