<?php

namespace Core\Packages\product;

use App\Models\User\User;
use App\Traits\VersionObserve;
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
	use VersionObserve, SoftDeletes;

	const KIND_AMOUNT     = 'amount';
	const KIND_PERCENTAGE = 'percentage';
	const KIND_BASKET     = 'basket';
	const KIND_VOLUMETRIC     = 'volumetric';
	const KIND_ROW     = 'row';
    const KIND_PERCENTAGE_STRIP     = 'percentage_strip';
    const KIND_KALAI     = 'kalai';

	const BASKET_KIND_PRODUCT  = 'product';
	const BASKET_KIND_CATEGORY = 'category';

	const STATUS_ACTIVE   = 'active';
	const STATUS_INACTIVE = 'inactive';

	const KINDS = [
		self::KIND_AMOUNT,
		self::KIND_PERCENTAGE,
		self::KIND_BASKET,
		self::KIND_VOLUMETRIC,
		self::KIND_ROW,
		self::KIND_PERCENTAGE_STRIP,
        self::KIND_KALAI
	];

	const BASKET_KINDS = [
		self::BASKET_KIND_PRODUCT,
		self::BASKET_KIND_CATEGORY,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	protected $appends = [
		'kind_translate',
	];

	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function Category()
	{
		return $this->belongsToMany(Category::class, 'promotions_category')
			->withPivot(['master', 'slave', 'slave2', 'total']);
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
}
