<?php

namespace App\Models\Order;

use App\Models\Setting\Constant;
use App\Models\Order\PaymentMethodCustomer;
use Illuminate\Database\Eloquent\Model;
use Core\Packages\user\Users;
/**
 * Class PaymentMethod
 *
 * @package App\Models\Order
 *
 * @property string $payment_method_id
 * @property string $company_id
 * @property string $discount
 * @property string $discount_max
 */
class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_method_id',
        'discount',
        'discount_max',
        'constant_en',
        'constant_fa',
        'kind',
    ];
	public function PaymentMethod()
	{
		return $this->belongsTo(Constant::class, 'payment_method_id');
	}

    public function PaymentMethodCustomer()
    {
        return $this->hasMany( PaymentMethodCustomer::class,'payment_method_id','payment_method_id');
    }

	public function Company()
	{
		return $this->belongsTo(Users::class, 'company_id');
	}

	public function getDiscount($finalPrice)
	{
		if ($this->discount == 0) {
			if ($finalPrice < $this->discount_max)
				return $finalPrice;

			return $this->discount_max;
		}

		$percentage = $this->discount / 100;

		$discount = $finalPrice * $percentage;

		if ($discount > $this->discount_max)
			return $this->discount_max;

		return $discount;
	}
}
