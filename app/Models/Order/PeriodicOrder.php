<?php

namespace App\Models\Order;

use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PeriodicOrder
 *
 * @package App\Models\Order
 * @property int              $id
 *
 * @property string           $customer_id
 * @property User             $customer
 *
 * @property string           $company_id
 * @property User             $company
 *
 * @property string           $days
 *
 * @property PeriodicDetail[] $details
 *
 * @property string           $status
 *
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 *
 * @method static Order CompanyId(integer $company_id)
 * @method static Order CustomerId(integer $user_id)
 */
class PeriodicOrder extends Model
{
	use SoftDeletes;

	const STATUS_REGISTERED = 'registered';

	const STATUS = [
		self::STATUS_REGISTERED,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'days',
	];

	protected $casts = [
		'days' => 'array',
	];

	// ********************************* Relations *********************************

	public function Customer()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	public function Details()
	{
		return $this->hasMany(PeriodicDetail::class, 'order_id');
	}

	// ********************************* Scope *********************************

	public function scopeCompanyId($query, $company_id = NULL)
	{
		if ($company_id) {
			return $query->where('company_id', $company_id);
		}

		return $query;
	}

	public function scopeCustomerId($query, $user_id = NULL)
	{
		if ($user_id) {
			return $query->where('customer_id', $user_id);
		}

		return $query;
	}

	// ********************************* Scope *********************************

	public function getStatusTranslateAttribute()
	{
		return trans("translate.setting.constant.$CONSTANT_KIND");
	}
}
