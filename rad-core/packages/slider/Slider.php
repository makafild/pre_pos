<?php


namespace Core\Packages\slider;

use Carbon\Carbon;
use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Core\Packages\user\Users;
use Core\Packages\common\File;
use Core\Packages\gis\Country;
use EloquentFilter\Filterable;
use Hekmatinasser\Verta\Verta;
use Core\Packages\gis\Province;
use Core\Packages\common\Constant;
use Core\Packages\gis\Routes;
use Core\Packages\product\Product;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\HelperTrait;
use Core\System\Exceptions\CoreException;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Slider
 *
 * @package App\Models\Common
 * @property int        $id
 *
 * @property string     $kind
 *
 * @property int        $file_id
 * @property File       $file
 *
 * @property int        $company_id
 * @property User       $company
 *
 * @property int        $product_id
 * @property Product    $product
 *
 * @property string     $link
 *
 * @property string     $status
 *
 * @property Country[]  $countries
 * @property Province[] $provinces
 * @property City[]     $cities
 * @property Constant[] $categories
 *
 * @property Carbon     $start_at
 * @property Carbon     $end_at
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @method static Slider Active()
 */
class Slider extends Model
{
    use SoftDeletes, Filterable, HelperTrait;

    const STATUS_ACTIVE    = 'active';
    const STATUS_INACTIVE    = 'inactive';


    const STATUS = [
        "active" => self::STATUS_ACTIVE,
        "inactive" => self::STATUS_INACTIVE,
    ];
    protected $appends = [
        "status_translate",
        'kind_translate',
    ];
    const KIND_LINK    = 'link';
    const KIND_COMPANY = 'company';
    const KIND_PRODUCT = 'product';

    const KINDS = [
        self::KIND_LINK,
        self::KIND_COMPANY,
        self::KIND_PRODUCT,
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
            self::$_instance = new Slider();
        }
        return self::$_instance;
    }
    public function list($id = '')
    {
        $query = $this->orderBy('id', 'desc');

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result = $query->with(
                'file',
                'Route',
                'Company',
                'Product',
                'Countries',
                'Areas',
                'Provinces',
                'Cities',
                'Categories',
            )->where('id', $id)->first();
        } else {
            $result = $query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id) ? 1 : count($result)]);
    }
    public function getKindTranslateAttribute()
    {
        return trans("translate.slider.kind.$this->kind");
    }
    public function Company()
    {
        return $this->belongsTo(Users::class)->withTrashed();
    }

    public function Product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function File()
    {
        return $this->belongsTo(File::class, 'file_id');
    }


    public function Countries()
    {
        return $this->belongsToMany(Country::class, 'slider_country');
    }

    public function Provinces()
    {
        return $this->belongsToMany(Province::class, 'slider_province');
    }
    public function Areas()
    {
        return $this->belongsToMany(Areas::class, 'slider_area');
    }
    public function Route()
    {
        return $this->belongsToMany(Routes::class, 'slider_route', 'slider_id', 'route_id');
    }


    public function Cities()
    {
        return $this->belongsToMany(City::class, 'slider_city');
    }

    public function Categories()
    {
        return $this->belongsToMany(Constant::class, 'slider_category');
    }


    // ********************************* Attributes *********************************


    // ********************************* Scope *********************************

    public function scopeActive($query)
    {
        return $query->whereDate('start_at', '<', Carbon::now())
            ->whereDate('end_at', '>', Carbon::now())
            ->where('status', self::STATUS_ACTIVE);
    }
    public function getStatusAttribute($value)
    {


        foreach (self::STATUS as $key => $status) {
            if ($value == $status) {
                return $key;
            }
        }
    }

    public function getStartAtAttribute($value)
    {
        $da = new Verta($value);
        return $da->formatDate();
    }


    public function getEndAtAttribute($value)
    {
        $da = new Verta($value);
        return $da->formatDate();
    }
    public function getStatusTranslateAttribute($value)
    {
        foreach (self::STATUS as $key => $status) {
            if ($this->status == $key) {
                return trans('translate.slider.status.' . $key);
            }
        }
    }
}
