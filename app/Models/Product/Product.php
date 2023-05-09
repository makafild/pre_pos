<?php

namespace App\Models\Product;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Common\File;
use App\Traits\VersionObserve;
use App\Models\User\PriceClass;
use App\Models\Setting\Constant;
use Awobaz\Compoships\Compoships;
use Illuminate\Support\Facades\DB;
use Core\Packages\promotion\Reward;
use App\Models\Product\ProductVisit;
use Core\Packages\category\Category;
use Illuminate\Database\Eloquent\Model;
use Core\Packages\promotion\RewardBrand;
use Core\Packages\promotion\RewardProduct;
use Core\Packages\promotion\RewardCategory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Core\Packages\promotion\RewardProductAward;

/**
 * Class Product
 *
 * @package App\Models\Product
 * @property int          $id
 *
 * @property string       $name_fa
 * @property string       $name_en
 * @property string       $description
 *
 * @property int          $per_master
 * @property int          $per_slave
 *
 * @property int          $master_unit_id
 * @property int          $slave_unit_id
 * @property int          $slave2_unit_id
 *
 * @property Constant     $master_unit
 * @property Constant     $slave_unit
 * @property Constant     $slave2_unit
 *
 * @property int          $quotas_master
 * @property int          $quotas_slave
 * @property int          $quotas_slave2
 *
 * @property int          $master_status
 * @property int          $slave_status
 * @property int          $slave2_status
 *
 * @property int          $price
 * @property int          $purchase_price
 * @property int          $sales_price
 * @property int          $consumer_price
 * @property int          $discount
 *
 * @property int          $brand_id
 * @property Brand        $brand
 *
 * @property int          $category_id
 * @property Category     $category
 *
 * @property int          $photo_id
 * @property File         $photo
 *
 * @property int          $company_id
 * @property User         $company
 *
 * @property PriceClass[] $price_classes
 *
 * @property int          $referral_id
 * @property int          $order_column
 *
 * @property string       $score
 * @property string       $status
 * @property string       $show_status
 * @property boolean      $has_user_category
 *
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property Carbon       $deleted_at
 *
 * @method static Product Available()
 * @method static Product Order(string $order)
 * @method static Product hasPromotions()
 * @method static Product CategoryIds(integer | integer[] $category_ids)
 * @method static Product SearchBarcode(string $barcode)
 * @method static Product CompanyId(integer $company_id)
 * @method static Product SearchName(string $title)
 * @method static Product SearchBrandByIds(integer | integer[] $brandIds)
 * @method static Product UserCategoryIds(integer[] $userCategoryIds)
 * @method static Product ReferralId(integer | array $referral_id)
 */
class Product extends Model
{
    use VersionObserve, SoftDeletes;
    use Compoships;

	const STATUS_AVAILABLE = 'available';
	const STATUS_UNAVAILABLE = 'unavailable';

	const STATUS = [
		self::STATUS_AVAILABLE,
		self::STATUS_UNAVAILABLE,
	];

    protected $fillable = [
        'api_service',
        'referral_id',
        'serial',
        'number_of_page',
        'isbn',
        'weight',
        'master_unit_id',
        'description',
        'sales_price',
        'consumer_price',
        'name_fa',
        'brand_id',
        'product_type_2',
        'company_id',
        'product_id',
        'sublayer_id',
	    'order_column'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $appends = [
        'price',
        'markup_price'
        ];

	protected $casts = [
		'score' => 'double',
	];
//
//	protected $casts = [
//		'purchase_price' => 'int',
//		'sales_price'    => 'int',
//		'consumer_price' => 'int',
//		'score'          => 'int',
//		'per_master'     => 'int',
//		'per_slave'      => 'int',
//	];

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

	public function Photos()
	{
		return $this->belongsToMany(File::class, 'product_photo', 'product_id', 'photo_id');
	}

	public function UserCategories()
	{
		return $this->belongsToMany(Constant::class, 'product_user_category');
	}

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
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

    public function RewardProduct()
    {
        return $this->hasMany(RewardProduct::class, ['product_id','company_id'],['id','company_id'])->whereHas('Reward',function($q){
           return $q->whereHas('promotion');
        });
    }

    public function PromotionsBrands()
	{
        return $this->belongsToMany(Promotions::class, 'promotions_brand','brand_id','promotions_id','brand_id','id')
        ->withPivot(['master', 'slave', 'slave2', 'total']);
	}




    public function RewardBrand()
    {
        return $this->hasMany(RewardBrand::class, ['brand_id','company_id'],['brand_id','company_id'])->whereHas('Reward',function($q){
             return $q->whereHas('promotion');
          });
    }
    public function RewardCategory()
    {
        return $this->hasMany(RewardCategory::class, ['category_id','company_id'],['category_id','company_id'])->whereHas('Reward',function($q){
             return $q->whereHas('promotion');
          });
    }
    public function RewardProductAward()
    {
        return $this->hasMany(RewardProductAward::class, ['product_id','company_id'],['id','company_id']);
    }

    public function Visit()
    {
        return $this->hasMany(ProductVisit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */



	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function PriceClasses()
	{
		return $this->belongsToMany(PriceClass::class, 'price_class_product', 'product_id', 'price_class_id')
			->withPivot(['price']);
	}


	// ********************************* Scope *********************************

	public function scopeActive($query)
	{
		return $query->where('show_status', User::STATUS_ACTIVE);
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
		if ($categoryIds){
            $query->where(function ($query) use ($categoryIds) {
                $query->whereHas('UserCategories', function ($query) use ($categoryIds) {
                    $query->whereIn('id', $categoryIds);
                })->orWhere('has_user_category', false);
            });
        }

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

            $keywords=explode(' ',$title);
        foreach($keywords as $keyword){
        $query->where('name_en', 'like', "%{$keyword}%")
        ->orWhere('name_fa', 'like', "%{$keyword}%")
        ->orWhere('serial', 'like', "%$keyword%");
    }

		});

		return $query;
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
		})->orWhereHas('PromotionsCategory', function ($query) {
			$query->where('status', Promotions::STATUS_ACTIVE);
		})->orWhereHas('PromotionsBrands', function ($query) {
			$query->where('status', Promotions::STATUS_ACTIVE);
		});
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

	// ********************************* Attributes *********************************

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


    public function Type1()
    {
        return $this->belongsToMany(Constant::class,'product_type_1', 'product_id','type_1');
    }

    public function Type2()
    {
        return $this->belongsTo(Constant::class, 'product_type_2');
    }

   /* public function getPromotionsAttribute()
    {
        $promotion = Promotions::select(['id', 'title', 'description'])
            ->whereIn('id', $this->getPromotion())
            ->whereHas('Reward.product', function ($q) {
                $q->where('product_id', $this->id);
            })
            ->orwhereHas('Reward.brand', function ($q) {
                $q->where('brand_id', $this->brand_id);
            })
            ->orwhereHas('Reward.category', function ($q) {
                $q->where('category_id', $this->category_id);
            })->where('company_id', $this->company_id);;
        return $promotion->get();
    }*/






    private function  getPromotion()
    {

        $user = User::select(['*'])->with(['Categories', 'Provinces', 'Cities', 'Area', 'Route', 'PriceClasses'])->where('id', auth('mobile')->user()->id)->first();

        $promotions = Promotions::select(['*', 'promotions.id as promotions_id', 'user_promotion.use'])->where('status', Promotions::ACTIVE)
            ->where(function ($query) {
                $query->whereNull('repeat_total')->orwhereRaw('repeat_total > count_use');
            })
            ->where(function ($query) {
                $query->whereNull('from_date')->orwhereRaw('from_date <= NOW()');
            })
            ->where(function ($query) {
                $query->whereNull('to_date')->orwhereRaw('to_date >= NOW()');
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('provinces')->orwhereJsonContains('provinces', (count($user->Provinces)) ? $user->Provinces->pluck('id')->toArray() : 'NULL');
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('city')->orwhereJsonContains('city', (count($user->Cities)) ? $user->Cities->pluck('id')->toArray() : 'NULL');
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('area')->orwhereJsonContains('area', (count($user->Area)) ? $user->Area->pluck('id')->toArray() : 'NULL');
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('route')->orwhereJsonContains('route', (count($user->Route)) ? $user->Route->pluck('id')->toArray() : 'NULL');
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('customers')->orwhereJsonContains('customers', $user->id);
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('price_classes')->orwhereJsonContains('price_classes', (count($user->PriceClasses)) ? $user->PriceClasses->pluck('id')->toArray() : 'NULL');
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('customer_group')->orwhereJsonContains('customer_group', (count($user->Categories)) ? $user->Categories->pluck('id')->toArray() : 'NULL');
            })->leftjoin('user_promotion', function ($join) use ($user) {
                $join->on('promotions.id', '=', 'user_promotion.promotion_id')
                    ->where('user_promotion.user_id', $user->id);
            })->where(function ($query) {
                $query->whereNull('repeat_for_customer')->orwhere(function ($q) {
                    $q->orwhereNull('user_promotion.use')->orwhereRaw('repeat_for_customer > user_promotion.use');
                });
            });
        // dd($promotions->toSql(), $promotions->getBindings());

        $promotions = $promotions->orderBy('sequence', 'DESC')->get();
        if (count($promotions)) return $promotions->pluck('promotions_id')->toArray();
        return [];
    }
}
