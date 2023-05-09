<?php

namespace App\Models\Order;

use App\Models\User\User;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Coupon
 *
 * @package App\Models\Order
 *
 * @property string $kind
 * @property string $coupon
 * @property int    $percentage
 * @property int    $discount_max
 * @property int    $amount
 * @property int    $status
 * @property string $start_at
 * @property string $end_at
 *
 * @property int    $company_id
 * @property User   $company
 */
class Coupon extends Model
{
	use VersionObserve, SoftDeletes;

	const STATUS_ACTIVE   = 'active';
	const STATUS_INACTIVE = 'inactive';

	const STATUS = [
		self::STATUS_ACTIVE,
		self::STATUS_INACTIVE,
	];

	const KIND_PERCENTAGE = 'percentage';
//	const KIND_AMOUNT = 'amount';
//
//	const KINDS = [
//		self::KIND_PERCENTAGE,
//		self::KIND_AMOUNT,
//	];

	protected $appends = [
		'title',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	// ********************************* Relations *********************************

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function CouponCustomer()
	{
		return $this->hasMany(CouponCustomer::class, 'coupon_id', 'id');
	}

	// ********************************* Attributes *********************************

	public function getStartAtAttribute()
	{
		$v = new Verta($this->attributes['start_at']);

		return str_replace('-', '/', $v->formatDate());
	}

	public function getEndAtAttribute()
	{
		$v = new Verta($this->attributes['end_at']);

		return str_replace('-', '/', $v->formatDate());
	}

	// ********************************* Scope *********************************

	public function scopeActive($query)
	{
		return $query->whereDate('start_at', '<=', Carbon::now())
			->whereDate('end_at', '>=', Carbon::now())
			->where('status', self::STATUS_ACTIVE);
	}

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId) {
			return $query->where('company_id', $companyId);
		}

		return $query;
	}

	public static function isValid($coupon, $companyId, $customerId)
	{
		if (!$customerId)
			$customerId = auth()->id();

		/** @var Coupon $coupon */
		$coupon = Coupon::where([
			'coupon'     => $coupon,
			'company_id' => $companyId,
		])->with([
			'CouponCustomer' => function ($query) use ($customerId) {
				$query->where('user_id', '=', $customerId);
			},
		])->active()->first();

		if ($coupon) {
//			if (count($coupon->CouponCustomer))
//				return NULL;

			return $coupon;

		}

		return NULL;
	}

	public function getDiscount($finalPrice)
	{
		if ($this->percentage == 0) {
			if ($finalPrice < $this->discount_max)
				return $finalPrice;

			return $this->discount_max;
		}

		$percentage = $this->percentage / 100;

		$discount = $finalPrice * $percentage;

		if ($discount > $this->discount_max)
			return $this->discount_max;

		return $discount;
	}


	public function getTitleAttribute()
	{
		return "تخفیف $this->percentage درصدی ($this->coupon)";
	}
}
