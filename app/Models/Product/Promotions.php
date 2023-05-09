<?php

namespace App\Models\Product;

use App\Models\User\User;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Core\Packages\category\Category;
use Core\Packages\common\PriceClass;
use Core\Packages\gis\Areas;
use Core\Packages\gis\City;
use Core\Packages\gis\Province;
use Core\Packages\gis\Routes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Promotions
 *
 * @package App\Models\Product
 * @property string $kind
 * @property string $basket_kind
 * @property string $title
 * @property string $description
 * @property string productRowstatus
 *
 * @property int $discount
 * @property string $volumes
 * @property int $percentage_discount
 * @property int $amount
 * @property string $status
 *
 * @property int $company_id
 * @property User $company
 *
 * @property Category[] $category
 * @property Product[] $baskets
 * @property Product[] $awards
 */
class Promotions extends Model
{
    use VersionObserve, SoftDeletes;

    const KIND_AMOUNT = 'amount';
    const KIND_PERCENTAGE = 'percentage';
    const KIND_BASKET = 'basket';
    const KIND_VOLUMETRIC = 'volumetric';
    const KIND_ROW = 'row';
    const KIND_PERCENTAGE_STRIP = 'percentage_strip';
    const KIND_PERCENTAGE_PRODUCT = 'percentage_product';
    const KIND_KALAI = 'kalai';

    const BASKET_KIND_PRODUCT = 'product';
    const BASKET_KIND_CATEGORY = 'category';
    const BASKET_KIND_BRAND = 'brand';

    const STATUS_ACTIVE = 'active';
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
            ->withPivot(['master', 'slave', 'slave2', 'total', 'amount', 'discount', 'discount_variables']);
    }

    public function Brand()
    {
        return $this->belongsToMany(Brand::class, 'promotions_brand')
            ->withPivot(['master', 'slave', 'slave2', 'total', 'amount', 'discount', 'discount_variables']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function Baskets()
    {
        return $this->belongsToMany(Product::class, 'promotions_baskets')
            ->withPivot(['master', 'slave', 'slave2', 'total', 'discount_variables']);
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


    public function checkExists($data, $curentUserData)
    {
        $flag = false;
        foreach ($data as $d) {
            if (in_array($d['id'], $curentUserData)) {
                $flag = true;
            }
        }
        return $flag;
    }

    public function hasAlowed()
    {

        $userInfo = User::where('id', auth('mobile')->id())->with(['Provinces', 'Cities', 'Areas', 'Routes', 'PriceClasses'])->first();

        $curentUserProvinces = [];
        $curentUserCities = [];
        $curentUserAreas = [];
        $curentUserRoutes = [];
        $curentUserPriceClasses = [];

        if (!empty($userInfo)) {
            $userInfo = $userInfo->toArray();
            if (!empty($userInfo['provinces'])) {
                foreach ($userInfo['provinces'] as $province) {
                    $curentUserProvinces[] = $province['id'];
                }
            }

            if (!empty($userInfo['cities'])) {
                foreach ($userInfo['cities'] as $city) {
                    $curentUserCities[] = $city['id'];
                }
            }

            if (!empty($userInfo['areas'])) {
                foreach ($userInfo['areas'] as $area) {
                    $curentUserAreas[] = $area['area_id'];
                }
            }

            if (!empty($userInfo['routes'])) {
                foreach ($userInfo['routes'] as $route) {
                    $curentUserRoutes[] = $route['route_id'];
                }
            }

            if (!empty($userInfo['price_classes'])) {

                foreach ($userInfo['price_classes'] as $PriceClasse) {
                    $curentUserPriceClasses[] = $PriceClasse['id'];
                }
            }
        }

        $promotions = Promotions::with([
            'Provinces',
            'Cities',
            'Areas',
            'Routes',
            'Customers',
            'PriceClasses'
        ])
            ->whereDate('from_date', '<=', Carbon::now())
            ->whereDate('to_date', '>=', Carbon::now())
            ->get();
        $promotionsFilter = [];
        foreach ($promotions->toArray() as $promotion) {
            $cPr = $promotion['provinces'];
            $cCi = $promotion['cities'];
            $cAr = $promotion['areas'];
            $cRo = $promotion['routes'];
            $cCu = $promotion['customers'];
            $cPri = $promotion['price_classes'];

            $sPr = count($cPr) ? true : false;
            $sCi = count($cCi) ? true : false;
            $sAr = count($cAr) ? true : false;
            $sRo = count($cRo) ? true : false;
            $sCu = count($cCu) ? true : false;
            $sPri = count($cPri) ? true : false;

            $sCount = 0;
            $x = 0;
            if ($sPr) {
                $sCount++;
                if ($this->checkExists($cPr, $curentUserProvinces)) {
                    $x++;
                }
            }

            if ($sCi) {
                $sCount++;
                if ($this->checkExists($cCi, $curentUserCities)) {
                    $x++;
                }
            }

            if ($sAr) {
                $sCount++;
                if ($this->checkExists($cAr, $curentUserAreas)) {
                    $x++;
                }
            }

            if ($sRo) {
                $sCount++;
                if ($this->checkExists($cRo, $curentUserRoutes)) {
                    $x++;
                }
            }

            if ($sCu) {
                $sCount++;
                if ($this->checkExists($cCu, [auth('mobile')->id()])) {
                    $x++;
                }
            }

            if ($sPri) {
                $sCount++;
                if ($this->checkExists($cPri, $curentUserPriceClasses)) {
                    $x++;
                }
            }

            if ($sCount != 0 && $sCount === $x) {
                $promotionsFilter[] = $promotion['id'];
            }
        }

        return $promotionsFilter;
    }

    public function parentsCategoory($categoryIds)
    {
        $nestedCategoryIds = [];
        $nestedCategoryIdsAll = [];
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $ci = [];
                $a = Category::where('id', $categoryId)->first();
                if (!is_null($a['parent_id'])) {
                    $ci[] = $a['id'];
                    $nestedCategoryIds[] = $a['id'];
                    $b = Category::where('id', $a['parent_id'])->first();
                    if (!is_null($b['parent_id'])) {
                        $ci[] = $b['id'];
                        $nestedCategoryIds[] = $b['id'];
                        $c = Category::where('id', $b['parent_id'])->first();
                        if (!is_null($c['parent_id'])) {
                            $ci[] = $c['id'];
                            $nestedCategoryIds[] = $c['id'];
                            $d = Category::where('id', $c['parent_id'])->first();
                            if (!is_null($d['parent_id'])) {
                                $ci[] = $d['id'];
                                $nestedCategoryIds[] = $d['id'];
                                $e = Category::where('id', $d['parent_id'])->first();
                                if (!is_null($e['parent_id'])) {
                                    $ci[] = $e['id'];
                                    $nestedCategoryIds[] = $e['id'];
                                    $f = Category::where('id', $e['parent_id'])->first();
                                    if (!is_null($f['parent_id'])) {
                                        $ci[] = $f['id'];
                                        $nestedCategoryIds[] = $f['id'];
                                    }
                                }
                            }
                        }
                    }
                }
                $nestedCategoryIdsAll[$categoryId] = $ci;
            }
        }
        return [
            'nestedCategoryIds' => $nestedCategoryIds,
            'nestedCategoryIdsAll' => $nestedCategoryIdsAll,
        ];
    }

    public static function check($products)
    {
        if (isset($_GET['o'])) {
            (new Promotions)->hasAlowed();
        }

        $products = collect($products)->keyBy('id');
        $productIds = $products->pluck('id')->all();

        /** @var Product[] $productEntities */
        $productEntities = Product::whereIn('id', $productIds)
            ->get();

        $categoryIds = $productEntities->pluck('category_id')
            ->unique()
            ->all();


        $brandIds = $productEntities->pluck('brand_id')
            ->unique()
            ->all();

        $cIds = (new Promotions)->parentsCategoory($categoryIds)['nestedCategoryIds'];

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
            ->where(function ($query) use ($productIds, $cIds, $brandIds) {
                $query->where(function ($query) use ($productIds) {
                    $query->whereHas('baskets', function ($query) use ($productIds) {
                        $query->whereIn('id', $productIds);
                    });
                })
                    ->orWhere(function ($query) use ($cIds) {
                        $query->orWhereHas('category', function ($query) use ($cIds) {
                            $query->whereIn('id', $cIds);
                        });
                    })
                    ->orWhere(function ($query) use ($brandIds) {
                        $query->orWhereHas('brand', function ($query) use ($brandIds) {
                            $query->whereIn('id', $brandIds);
                        });
                    });
            });

        $promotions = $promotions->whereIn('id', (new Promotions)->hasAlowed());
        $promotions = $promotions->get();

        $finalPromotions = [
            Promotions::KIND_PERCENTAGE => [],
            Promotions::KIND_AMOUNT => [],
            Promotions::KIND_BASKET => [],
            Promotions::KIND_PERCENTAGE_STRIP => [],
            Promotions::KIND_VOLUMETRIC => [],
            Promotions::KIND_ROW => [],
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

                    $sameCategoryProducts = $productEntities;
                    foreach ($sameCategoryProducts as $sameCategoryProduct) {

                        if (in_array($category->id, (new Promotions)->parentsCategoory($categoryIds)['nestedCategoryIdsAll'][$sameCategoryProduct->category_id])) {
                            $catId = (new Promotions)->parentsCategoory($categoryIds)['nestedCategoryIdsAll'][$sameCategoryProduct->category_id][0];
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
            } else if ($promotion->basket_kind == Promotions::BASKET_KIND_BRAND) {

                foreach ($promotion->brand as $brand) {
                    $sameBrandProducts = $productEntities->where('brand_id', $brand->id);

                    foreach ($sameBrandProducts as $sameBrandProduct) {
                        if ($sameBrandProduct->company_id != $promotion->company_id)
                            continue;

                        $product = $products[$sameBrandProduct->id];

                        // calculate total of product of order
                        $total = Product::calculateTotal(
                            $product['master'],
                            $product['slave'],
                            $product['slave2'],
                            $sameBrandProduct->per_master,
                            $sameBrandProduct->per_slave
                        );

                        if ($total >= $brand->pivot->total) {
                            $basketBreak = true;
                            break;
                        }
                    }
                }
            }

            if ($basketBreak) {

                if ($promotion->kind == Promotions::KIND_PERCENTAGE) {
                    if (count($promotion->baskets)) {
                        $finalPromotions[$promotion->kind][$promotion->baskets[0]->id] = $promotion;
                    }

                    if (count($promotion->category)) {

                        $productCategories = Product::where('category_id', $catId)->pluck('id')->all();
                        foreach ($productCategories as $productCategory) {
                            $finalPromotions[$promotion->kind][$productCategory] = $promotion;
                        }
                    }

                    if (count($promotion->brand)) {

                        $productBrands = Product::where('brand_id', $promotion->brand[0]->id)->pluck('id')->all();

                        foreach ($productBrands as $productBrand) {

                            $finalPromotions[$promotion->kind][$productBrand] = $promotion;
                        }
                    }
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

        return $value ? json_decode($value) : null;
    }

    public function getRowProductStatusAttribute($value)
    {
        return $value ? json_decode($value) : null;
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
        return $this->belongsToMany(Areas::class, 'promotions_area', 'promotion_id', 'area_id');
    }

    public function Routes()
    {
        return $this->belongsToMany(Routes::class, 'promotions_route', 'promotion_id', 'route_id');
    }

    public function Customers()
    {
        return $this->belongsToMany(User::class, 'promotions_customer', 'promotion_id', 'customer_id');
    }


    public function PriceClasses()
    {
        return $this->belongsToMany(PriceClass::class, 'promotions_price_class', 'promotion_id', "price_class_id");
    }
}
