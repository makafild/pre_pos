<?php

namespace App\Models\Order;

use App\Models\Setting\Constant;
use App\Models\User\User;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * Class Order
 *
 * @package App\Models\Order
 * @property int           $id
 *
 * @property int           $price_without_promotions
 * @property int           $discount
 * @property int           $amount_promotion
 * @property int           $price_with_promotions
 * @property int           $final_price
 *
 * @property string        $description
 * @property string        $payment_confirm
 * @property string        $transfer_number
 * @property string        $carriage_fares
 * @property string        $imei
 *
 * @property int           $customer_id
 * @property User          $customer
 *
 * @property int           $payment_method_id
 * @property Constant      $PaymentMethod
 *
 * @property int           $new_payment_method_id
 * @property PaymentMethod $NewPaymentMethod
 *
 * @property int           $company_id
 * @property User          $company
 *
 * @property Detail[]      $details
 * @property Addition[]    $additions
 *
 * @property string        $status
 * @property string        $payment_status
 *
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon        $date_of_sending
 *
 * @method static Order CompanyId(integer $company_id)
 * @method static Order ReferralId(integer $referral_id)
 * @method static Order CustomerId(integer $user_id)
 */
class Order extends Model
{
	use VersionObserve, SoftDeletes;

	const STATUS_REGISTERED       = 'registered';
	const STATUS_CONFIRMED        = 'confirmed';
	const STATUS_POSTED           = 'posted';
	const STATUS_SEND_IN_PROGRESS = 'send_in_progress';
	const STATUS_REJECTED         = 'rejected';

	const STATUS = [
		self::STATUS_REGISTERED,
		self::STATUS_CONFIRMED,
		self::STATUS_POSTED,
		self::STATUS_SEND_IN_PROGRESS,
		self::STATUS_REJECTED,
	];

	const PAYMENT_DEFAULT      = 'default';
	const PAYMENT_SUCCESSFUL   = 'success';
	const PAYMENT_DEPENDING    = 'depending';
	const PAYMENT_UNSUCCESSFUL = 'unsuccessful';
	const PAYMENTS             = [
		self::PAYMENT_DEFAULT,
		self::PAYMENT_SUCCESSFUL,
		self::PAYMENT_DEPENDING,
		self::PAYMENT_UNSUCCESSFUL,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $appends = [
		'status_translate',
		'date_of_sending_translate',
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

	public function PaymentMethod()
	{
		return $this->belongsTo(Constant::class);
	}

	public function NewPaymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class, 'new_payment_method_id');
	}

	public function Details()
	{
		return $this->hasMany(Detail::class);
	}

	public function Additions()
	{
		return $this->hasMany(Addition::class);
	}

    public function OrderCompanyPriorities()
    {
        return $this->hasMany(OrderCompanyPriorities::class);
    }

	public function Coupon()
	{
		return $this->belongsTo(Coupon::class)->withTrashed();
	}

	// ********************************* Scope *********************************

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId) {
			return $query->where('company_id', $companyId);
		}

		return $query;
	}

	public function scopeReferralId($query, $referralId)
	{
		if ($referralId) {
			return $query->where('referral_id', $referralId);
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
		return trans("translate.setting.constant.{$this->status}");
	}

	public function getTrackerUrlAttribute($tracker_url)
	{
		if ($tracker_url) {
			$url = $this->company->api_url . $tracker_url;

			return $url;
		}

		return NULL;
	}

	public function getDateOfSendingTranslateAttribute()
	{
		if(!isset($this->attributes['date_of_sending']))
			return '';

		$v = new Verta($this->attributes['date_of_sending']);

		return str_replace('-', '/', $v->formatDate());
	}

}
