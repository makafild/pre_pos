<?php

namespace Core\Packages\common;





use Core\Packages\user\Users;
use App\Models\Order\Addition;
use Core\Packages\order\Order;
use EloquentFilter\Filterable;
use Core\Packages\order\Detail;
use Core\Packages\slider\Slider;
use Core\Packages\product\Product;
use Illuminate\Support\Facades\App;
// use App\Models\Order\AdditionInvoice;
use App\Models\Order\AdditionInvoice;
use Core\Packages\order\OrderInvoice;
use Core\Packages\order\DetailInvoice;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\HelperTrait;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\SecureDelete;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class Constant
 *
 * @package App\Models\Setting
 * @property int $id
 *
 * @property string $constant_fa
 * @property string $constant_en
 * @property string $kind
 *
 * @method static Constant Kind($kind)
 */
class Constant extends Model
{
    use SecureDelete;
    use SoftDeletes;
    use Filterable;
    use HelperTrait;
    

    const CUSTOMER_CATEGORY = 'customer_category';
    const UNIT = 'unit';
    const ADDITIONS = 'additions';
    const PRODUCT_LABEL = 'product_label';
    const PAYMENT_METHOD = 'payment_method';
    const INVOICE_TITLE = 'invoice_title';
    const SUBLAYER = 'sublayer';
    const TAX = 'tax';
    const DEDUCTIONS = 'deductions';
    const CUSTOMER_GROUP = 'customer_group';
    const CUSTOMER_CLASS = 'customer_class';
    const CUSTOMER_GRADE = 'customer_grade';
    const UNVISITED_DESCRIPTION = 'unvisited_description_id';
    const ORDER_REJECT_TEXT = 'order_reject_text';
    const INTRODUCTION_SOURCE = 'introduction_source';
    const PRODUCT_TYPE_1 = 'product_type_1';
    const PRODUCT_TYPE_2 = 'product_type_2';

    const CONSTANT_KINDS = [
        self::CUSTOMER_CATEGORY,
        self::UNIT,
        self::ADDITIONS,
        self::PRODUCT_LABEL,
        self::PAYMENT_METHOD,
        self::INVOICE_TITLE,
        self::SUBLAYER,
        self::TAX,
        self::DEDUCTIONS,
        self::CUSTOMER_GROUP,
        self::CUSTOMER_CLASS,
        self::CUSTOMER_GRADE,
        self::UNVISITED_DESCRIPTION,
        self::ORDER_REJECT_TEXT,
        self::INTRODUCTION_SOURCE,
        self::PRODUCT_TYPE_1,
        self::PRODUCT_TYPE_2
    ];
    protected $fillable = [
        'constant_en',
        'constant_fa',
        'kind',
        'company_id',
        'percent',
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Constant();
        }
        return self::$_instance;
    }

    protected $appends = [
        'kind_translate',
        'constant',
    ];

    public function scopeKind($query, $kind)
    {
        return $query->where('kind', $kind);
    }

    public function getKindTranslateAttribute()
    {
        return trans("translate.setting.constant.$this->kind");
    }

    public function getConstantAttribute()
    {
        $name = 'constant_' . \App::getLocale();

        return $this->attributes[$name];
    }

    public function list($id = '')
    {
        $query = $this->orderBy('id', 'desc');

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result = $query->where('id', $id)->first();
        } else {
            $result = $query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id) ? 1 : count($result)]);
    }

    public function destroyRecord($id)
    {

        return $this->destroyRow($id);
    }

    public function updateU($payload, $id)
    {
        return $this->updateRow($payload, $id);
    }






    public function Additions()
    {
        return $this->hasMany(Addition::class, 'key_id');
    }

    // public function AdditionInvoices()
    // {
    //     return $this->hasMany(AdditionInvoice::class , 'key_id');
    // }

    public function Details()
    {
        return $this->hasMany(Detail::class , 'master_unit_id');
    }

    public function DetailInvoices()
    {
        return $this->hasMany(DetailInvoice::class , 'master_unit_id');
    }
    // public function Orders()
    // {
    //     return $this->hasMany(Order::class);
    // }
    // public function OrderInvoices()
    // {
    //     return $this->hasMany(OrderInvoice::class);
    // }
    public function Products_one()
    {
        return $this->hasMany(Product::class , 'sublayer_id');
    }
    public function Products()
    {
        // return $this->belongsToMany(User::class, 'user_brand');
        return $this->belongsToMany(Product::class, 'product_user_category');
    }
    public function Products_many()
    {

        return $this->belongsToMany(Product::class, 'product_type_1' , "type_1");
    }
    public function Products_many_sec()
    {

        return $this->belongsToMany(Product::class, 'product_label' , 'label_id' );
    }
    public function Sliders()
    {
        return $this->belongsToMany(Slider::class, 'slider_category');
    }
    public function Users_many()
    {
        return $this->belongsToMany(Users::class, 'user_category' , 'constant_id','user_id');
    }
    public function Users()
    {
        return $this->belongsTo(Users::class);
    }
}
