<?php

namespace Core\Packages\order;

use Carbon\Carbon;
use App\Models\Order\Coupon;
use Core\Packages\user\Users;
use App\Models\Order\Addition;
use EloquentFilter\Filterable;
use Hekmatinasser\Verta\Verta;
use Core\Packages\common\Constant;
use App\Models\Order\PaymentMethod;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\HelperTrait;
use Core\System\Http\Traits\SecureDelete;
use App\Models\Order\OrderCompanyPriorities;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use SecureDelete;
    use SoftDeletes;
    use HelperTrait;
    use Filterable;
    const STATUS_REGISTERED       = 'registered';
    const KIND_VENDOR      = 'vendor';
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
    private static $_instance = null;
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Order();
        }
        return self::$_instance;
    }
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
    protected $hidden = ['date_id' , 'address_id' , 'kind'];

    protected $fillable = [
        'price_without_promotions',
        'promotion_price',
        'price_with_promotions',
        'amount_promotion',
        'discount',
        'amount_promotion',
        'total_row_discount',
        'final_price',
        'final_price_without_total_row_discount',
        'markup_price',
        'visitor_id',
        'customer_id',
        'company_id',
        'payment_method_id',
        'coupon_id',
        'tracker_url',
        'factor_id',
        'referral_id',
        'status',
        'reject_text_id',
        'date_of_sending',
        'description',
        'payment_confirm',
        'transfer_number',
        'carriage_fares',
        'new_payment_method_id',
        'imei',
        'row_version',
        'deliver',
        'deliver_date',
        'change_status_date',
        'version',
        'reference_date',
        'registered_by',
        'registered_source',
        'updated_by'
    ];

    // ********************************* Relations *********************************

    public function Customer()
    {
        return $this->belongsTo(Users::class)->withTrashed();
    }


    public function visitor()
    {
        return $this->hasOne(Visi::class, 'id','visitor_id');

    }
    public function invoiceOrder()
    {
        return $this->hasOne(OrderInvoice::class, 'id','id');
    }


    public function Company()
    {
        return $this->belongsTo(Users::class)->withTrashed();
    }

    public function PaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function NewPaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'new_payment_method_id');
    }

    public function scopeWhereIds($query, $id)
    {
        return  $query->whereIn('id', $id);
    }

    public function RejectText()
    {
        return $this->belongsTo(Constant::class, 'reject_text_id');
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
        if (!isset($this->attributes['date_of_sending']))
            return '';

        $v = new Verta($this->attributes['date_of_sending']);

        return str_replace('-', '/', $v->formatDate());
    }
}
