<?php

namespace Core\Packages\promotion;

use Core\Packages\brand\Brand;
use App\Models\User\User;
use Core\Packages\common\PriceClass;
use Core\Packages\gis\Areas;
use Core\Packages\gis\City;
use Core\Packages\gis\Province;
use Core\Packages\gis\Routes;
use Core\Packages\product\Product;
use Core\Packages\user\Users;
use Core\Packages\category\Category;
use Core\System\Http\Traits\HelperTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Promotions
 *
 * @package App\Models\Product
 * @property string     $kind
 * @property string     $basket_kind
 * @property string     $title
 * @property string     $description
 * @property string     productRowstatus
 *
 * @property int        $discount
 * @property string     $volumes
 * @property int        $percentage_discount
 * @property int        $amount
 * @property string     $status
 *
 * @property int        $company_id
 * @property User       $company
 *
 * @property Category[] $category
 * @property Product[]  $baskets
 * @property Product[]  $awards
 */
class Promotions extends Model
{
	use SoftDeletes;
    use Filterable;
    use HelperTrait;
	const KIND_AMOUNT     = 'amount';
	const KIND_PERCENTAGE = 'percentage';
	const KIND_BASKET     = 'basket';
	const KIND_VOLUMETRIC     = 'volumetric';
	const KIND_ROW     = 'row';
    const KIND_PERCENTAGE_STRIP     = 'percentage_strip';
    const KIND_PERCENTAGE_PRODUCT    = 'percentage_product';
    const KIND_KALAI     = 'kalai';

	const BASKET_KIND_PRODUCT  = 'product';
	const BASKET_KIND_CATEGORY = 'category';
	const BASKET_KIND_BRAND = 'brand';

	const STATUS_ACTIVE   = 'active';
	const STATUS_INACTIVE = 'inactive';

	const KINDS = [
		self::KIND_AMOUNT,
		self::KIND_PERCENTAGE,
		self::KIND_BASKET,
		self::KIND_VOLUMETRIC,
		self::KIND_ROW,
		self::KIND_PERCENTAGE_STRIP,
		self::KIND_PERCENTAGE_PRODUCT,
        self::KIND_KALAI
	];

	const BASKET_KINDS = [
		self::BASKET_KIND_PRODUCT,
		self::BASKET_KIND_CATEGORY,
		self::BASKET_KIND_BRAND
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
            self::$_instance = new Promotions();
        }
        return self::$_instance;
    }
	protected $appends = [
		'kind_translate',
	];

	public function Company()
	{
		return $this->belongsTo(Users::class)->withTrashed();
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function Category()
    {
        return $this->belongsToMany(Category::class, 'promotions_category')
            ->withPivot(['master', 'slave', 'slave2', 'total', 'amount','discount','discount_variables']);
    }

    public function Brand()
    {
        return $this->belongsToMany(Brand::class, 'promotions_brand')
            ->withPivot(['master', 'slave', 'slave2', 'total','amount','discount']);
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function Baskets()
	{
		return $this->belongsToMany(Product::class, 'promotions_baskets')
			->withPivot(['master', 'slave', 'slave2', 'total','discount_variables']);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function Awards()
	{
		return $this->belongsToMany(Product::class, 'promotions_awards')
			->withPivot(['master', 'slave', 'slave2', 'discount']);
	}

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId)
			return $query->where('company_id', $companyId);

		return $query;
	}

	/**
	 * @param $products
	 *
	 * @return Promotions[][]
	 */
	public static function check($products)
	{
		$products   = collect($products)->keyBy('id');
		$productIds = $products->pluck('id')->all();

		/** @var Product[] $productEntities */
		$productEntities = Product::whereIn('id', $productIds)
			->get();


		$categoryIds = $productEntities->pluck('category_id')
			->unique()
			->all();

		/** @var Promotions[] $promotions */
		$promotions = Promotions::with([
			'baskets',
			'awards',
			'awards.MasterUnit',
			'awards.SlaveUnit',
			'awards.Slave2Unit',
			'awards.Photo',
		])
			->where('status', Promotions::STATUS_ACTIVE)
			->where(function ($query) use ($productIds, $categoryIds) {
				$query
					->where(function ($query) use ($productIds) {
						$query->whereHas('baskets', function ($query) use ($productIds) {
							$query->whereIn('id', $productIds);
						});
					})
					->orWhere(function ($query) use ($categoryIds) {
						$query->orWhereHas('category', function ($query) use ($categoryIds) {
							$query->whereIn('id', $categoryIds);
						});
					});
			})
			->get();
		$finalPromotions = [
			Promotions::KIND_PERCENTAGE => [],
			Promotions::KIND_AMOUNT     => [],
			Promotions::KIND_BASKET     => [],
			Promotions::KIND_PERCENTAGE_STRIP     => [],
			Promotions::KIND_PERCENTAGE_PRODUCT     => [],
			Promotions::KIND_VOLUMETRIC     => [],
			Promotions::KIND_ROW     => [],
		];
		foreach ($promotions as $promotion) {

			$basketBreak = false;
			if ($promotion->basket_kind == Promotions::BASKET_KIND_PRODUCT) {
				foreach ($promotion->baskets as $basket) {
					if (!in_array($basket->id, $productIds)) {
						$basketBreak = false;
					} else {

						$product = $products[$basket->id];

						// calculate total of product of order
						$total = Product::calculateTotal(
							$product['master'],
							$product['slave'],
							$product['slave2'],
							$basket->per_master,
							$basket->per_slave
						);

						if ($total >= $basket->pivot->total) {
							$basketBreak = true;
							break;
						}
					}
				}
			} else if ($promotion->basket_kind == Promotions::BASKET_KIND_CATEGORY) {
				foreach ($promotion->category as $category) {
					/** @var Product[] $sameCategoryProducts */
					$sameCategoryProducts = $productEntities->where('category_id', $category->id);

					foreach ($sameCategoryProducts as $sameCategoryProduct) {
						if ($sameCategoryProduct->company_id != $promotion->company_id)
							continue;

						$product = $products[$sameCategoryProduct->id];

						// calculate total of product of order
						$total = Product::calculateTotal(
							$product['master'],
							$product['slave'],
							$product['slave2'],
							$sameCategoryProduct->per_master,
							$sameCategoryProduct->per_slave
						);

						if ($total >= $category->pivot->total) {
							$basketBreak = true;

							break;
						}
					}
				}
			}

			if ($basketBreak) {

				if ($promotion->kind == Promotions::KIND_PERCENTAGE) {
					if (count($promotion->baskets))

						$finalPromotions[$promotion->kind][$promotion->baskets[0]->id] = $promotion;
				} else {
					$finalPromotions[$promotion->kind][] = $promotion;
				}

			}
		}


		return $finalPromotions;
	}

	public function getKindTranslateAttribute()
	{
		return trans("translate.product.promotions.{$this->kind}");
	}
    public function destroyRecord($id){

        return $this->destroyRow($id);
    }
	public function getPercentageDiscountAttribute()
	{
		return $this->discount / 100;
	}
    public function getVolumesAttribute($value)
    {

        return $value  ? json_decode($value) : null;
    }

    public function getRowProductStatusAttribute($value)
    {
        return $value  ? json_decode($value) : null;
    }

    public function Provinces()
    {
        return $this->belongsToMany(Province::class, 'promotions_province', 'promotion_id');
    }

    public function Cities()
    {
        return $this->belongsToMany(City::class, 'promotions_city', 'promotion_id');
    }

    public function Areas()
    {
        return $this->belongsToMany(Areas::class, 'promotions_area', 'promotion_id','area_id');
    }

    public function Routes()
    {
        return $this->belongsToMany(Routes::class, 'promotions_route', 'promotion_id','route_id');
    }

    public function Customers()
    {
        return $this->belongsToMany(User::class, 'promotions_customer', 'promotion_id', "customer_id");
    }

    public function PriceClasses()
    {
        return $this->belongsToMany(PriceClass::class, 'promotions_price_class', 'promotion_id', "price_class_id");
    }
}
