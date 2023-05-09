<?php

namespace Core\Packages\product;

use Carbon\Carbon;
use Core\Packages\user\Users;
use Core\Packages\common\File;
use EloquentFilter\Filterable;
use Core\Packages\order\Detail;
use Core\Packages\common\Constant;
use Illuminate\Support\Facades\DB;
use App\Models\Product\ProductVisit;
use Core\Packages\category\Category;
use Core\Packages\company\PriceClass;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\HelperTrait;
use Core\System\Http\Traits\SecureDelete;
use Illuminate\Database\Eloquent\SoftDeletes;
use Core\Packages\report\ReportsSaleProductRoute;

class Product extends Model
{
    use SecureDelete;
    use SoftDeletes;
    use Filterable;
    use HelperTrait;
    const STATUS_AVAILABLE = 'available';
    const STATUS_UNAVAILABLE = 'unavailable';
    const SHOW_STATUS_ACTIVE = 'active';
    const SHOW_STATUS_INACTIVE = 'inactive';
    private static $_instance = null;
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Product();
        }
        return self::$_instance;
    }
    const STATUS = [
        self::STATUS_AVAILABLE => [
            'value' => self::STATUS_AVAILABLE,
            'color' => "success",
        ],
        self::STATUS_UNAVAILABLE => [
            'value' => self::STATUS_UNAVAILABLE,
            'color' => "danger",
        ]
    ];
    const SHOW_STATUS = [
        self::SHOW_STATUS_ACTIVE => [
            'value' => self::SHOW_STATUS_ACTIVE,
            'color' => "success",
        ],
        self::SHOW_STATUS_INACTIVE => [
            'value' => self::SHOW_STATUS_INACTIVE,
            'color' => "danger",
        ]
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'price',
        'status_translate',
        'markup_price',
        'show_status_translate',
    ];

    protected $casts = [
        'score' => 'double',
    ];

    public function Brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function Category()
    {
        return $this->belongsTo(Category::class);
    }

    public function Photo()
    {
        return $this->belongsTo(File::class);
    }

    public function Sublayer()
    {
        return $this->belongsTo(Constant::class);
    }

    public function Photos()
    {
        return $this->belongsToMany(File::class, 'product_photo', 'product_id', 'photo_id');
    }
    public function Details()
    {
        return $this->hasMany(Detail::class);
    }

    public function UserCategories()
    {
        return $this->belongsToMany(Constant::class, 'product_user_category');
    }
    public function ReportsSaleProductRoute()
    {
        return $this->hasMany(ReportsSaleProductRoute::class, 'product_id', 'id');
    }

    public function Type1()
    {
        return $this->belongsToMany(Constant::class, 'product_type_1', 'product_id', 'type_1');
    }

    public function Type2()
    {
        return $this->belongsTo(Constant::class, 'product_type_2', 'id');
    }

    public function Company()
    {
        return $this->belongsTo(Users::class)->withTrashed();
    }

    public function Users()
    {
        return $this->hasOne(Users::class, 'id', 'company_id');
    }



    public function MasterUnit()
    {
        return $this->belongsTo(Constant::class);
    }

    public function SlaveUnit()
    {
        return $this->belongsTo(Constant::class);
    }

    public function Slave2Unit()
    {
        return $this->belongsTo(Constant::class);
    }

    public function Labels()
    {
        return $this->belongsToMany(Constant::class, 'product_label', 'product_id', 'label_id');
    }

    public function Barcodes()
    {
        return $this->hasMany(Barcode::class);
    }

    public function Visit()
    {
        return $this->hasMany(ProductVisit::class);
    }

    public function Promotions()
    {
        return $this->belongsToMany(Promotions::class, 'promotions_baskets')
            ->withPivot(['master', 'slave', 'slave2', 'total']);
    }

    public function PriceClasses()
    {
        return $this->belongsToMany(PriceClass::class, 'price_class_product', 'product_id', 'price_class_id')
            ->withPivot(['price']);
    }

    public function scopeActive($query)
    {
        return $query->where('show_status', Users::STATUS_ACTIVE);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeCompanyId($query, $companyId)
    {
        if ($companyId === false)
            return $query->where('company_id', false);

        if ($companyId)
            return $query->where('company_id', $companyId);

        return $query;
    }

    public function scopeWhereNameCompany($query, $name)
    {

        return $query->whereHas('Users', function ($query) use ($name) {
            $query->where('name_fa', '=', $name);
        });
    }

    public function scopeSearchBarcode($query, $barcode)
    {
        if ($barcode)
            $query->whereHas('Barcodes', function ($query) use ($barcode) {
                $query->where('barcode', $barcode);
            });

        return $query;
    }

    public function scopeCategoryIds($query, $categoryIds = NULL)
    {
        if ($categoryIds) {
            if (!is_array($categoryIds))
                $categoryIds = [$categoryIds];

            return $query->whereIn('category_id', $categoryIds);
        }

        return $query;
    }

    public function scopeUserCategoryIds($query, $categoryIds = NULL)
    {
        $query->where(function ($query) use ($categoryIds) {
            $query->whereHas('UserCategories', function ($query) use ($categoryIds) {
                $query->whereIn('id', $categoryIds);
            })->orWhere('has_user_category', false);
        });

        return $query;
    }

    public function scopeReferralId($query, $referralId)
    {
        if (!is_array($referralId))
            $referralId = [$referralId];

        return $query->whereIn('referral_id', $referralId);
    }

    public function scopeSearchName($query, $title = NULL)
    {
        if (!$title)
            return $query;

        $query->where(function ($query) use ($title) {
            $query->where('name_en', 'like', "%{$title}%")
                ->orWhere('name_fa', 'like', "%{$title}%");
        });

        return $query;
    }
    public function destroyRecord($id)
    {
        return $this->destroyRow($id);
    }
    public function scopeSearchBrandByIds($query, $brandIds)
    {
        if (!$brandIds || (is_array($brandIds) && !count($brandIds)))
            return $query;

		if (!is_array($brandIds))
			$brandIds = [$brandIds];

		return $query->whereIn('brand_id', $brandIds);
	}

	public function scopeHasPromotions($query)
	{
		return $query->whereHas('promotions', function ($query) {
			$query->where('status', Promotions::STATUS_ACTIVE);
		});
	}
    public function scopeWhereIds($query, $id)
    {
        return  $query->whereIn('id', $id);
    }

	public function scopeOrder($query, $order)
	{
		switch ($order) {
			case 'newest':
				return $query->orderBy('created_at', 'desc');
				break;
			case 'cheapest':
				return $query->orderBy('sales_price', 'asc');
				break;
			case 'expensive':
				return $query->orderBy('sales_price', 'desc');
				break;
			case 'profit':
				return $query->orderByRaw('((consumer_price - sales_price) / consumer_price) desc');
				break;
			case 'best-sales':
//				return $query->orderBy('sales_price', 'desc');
//				$query->join('details', 'products.id', '=', 'details.product_id');
//				$query->groupBy('details.product_id');
//				$query->orderByRaw('sum(details.total) desc');
////				$query->pluck('Product');

				return $query;
				break;
			case 'referral_id':
			default:
				return $query->orderBy('order_column', 'asc');
				break;
		}
	}

	public function getPriceAttribute()
	{
		if ($this->relationLoaded('PriceClasses')) {
			foreach ($this->PriceClasses as $priceClass) {
				if ($priceClass->relationLoaded('Customers'))
					if ($priceClass->Customers->count())
						return $priceClass->pivot->price;
			}
		}

		return $this->sales_price;
	}

	public function getMarkupPriceAttribute()
	{
	    $markup=$this->consumer_price - $this->price;
	    if ($markup<0){
	        return 0;
        }
		return $markup;

	}

	public static function calculateTotal($master, $slave = 0, $slave2 = 0, $per_master = 0, $per_slave = 0)
	{
		$total = $master;
		if ($per_master) {
			$total = $total * $per_master;
		}
		$total += $slave;

		if ($per_slave) {
			$total = $total * $per_slave;
		}
		$total += $slave2;
		return $total;
	}

	public static function calculateTotalById($id, $master, $slave = 0, $slave2 = 0)
	{
		/** @var Product $product */
		$product = Product::find($id);

		$total = $master;
		if ($product->per_master) {
			$total = $total * $product->per_master;
		}
		$total += $slave;

		if ($product->per_slave) {
			$total = $total * $product->per_slave;
		}
		$total += $slave2;

		return $total;
	}

	public static function minimUnit($master, $slave = 0, $slave2 = 0, $per_master = 0, $per_slave = 0)
	{
		$slaveOverFlow = 0;
		if ($per_slave) {
			$slaveOverFlow = floor($slave2 / $per_slave);
			$slave2 = $slave2 % $per_slave;
		}

		$slave = $slave + $slaveOverFlow;
		$masterOverFlow = 0;
		if ($per_master) {
			$masterOverFlow = floor($slave / $per_master);
			$slave = $slave % $per_master;
		}

        $master = $master + $masterOverFlow;

        return [$master, $slave, $slave2];
    }

    public function setScoreAttribute($score)
    {
        $score = round($score, 1);

        $score = str_replace('.0', '', $score);

        $this->attributes['score'] = $score;
    }

    public function getStatusTranslateAttribute($value)
    {
        foreach (self::STATUS as $key => $status) {
            if ($this->status == $key) {
                return trans('translate.product.status.' . $key);
            }
        }
    }

    public function getShowStatusTranslateAttribute($value)
    {
        foreach (self::SHOW_STATUS as $key => $status) {

            if ($this->show_status == $key) {
                return trans('translate.product.show_status.' . $key);
            }
        }
    }
}
