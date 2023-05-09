<?php

namespace App\Http\Controllers\api\Customer\v1\Product;

use Carbon\Carbon;
use App\Models\User\Role;
use App\Models\User\User;
use App\Traits\CheckAccess;
use Illuminate\Http\Request;
use App\Models\Product\Score;
use App\Models\Product\Product;
use App\Models\Product\Promotions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductVisit;
use Core\Packages\customer\Loglogin;
use Illuminate\Database\Eloquent\Model;
use App\Http\Requests\api\Company\v1\Product\ProductVisitRequest;
use App\Http\Requests\api\Customer\v1\Company\ScoreCompanyRequest;

class ProductController extends Controller
{
    use CheckAccess;



    public function index(Request $request , $limit = null)
    {



        // Loglogin::updateOrC        Loglogin::updateOrCreate(
        //     ["user_id" => auth('mobile')->user()->id],
        //     [
        //         "user_id" => auth('mobile')->user()->id,
        //         "created_at" => now()
        //     ]
        // );
        $limit = $request->limit;

        if (auth('mobile')->guest()) {
            if(!$request->paginate){
   $products = Product::Active()
                ->withCount('Visit')
                ->with([
                    'brand',
                    'category',
                    'photo',
                    'photos',
                    'company',
                    'labels',
                    'MasterUnit',
                    'SlaveUnit',
                    'Slave2Unit',

                ]);
                $products = $products->orderByRaw("FIELD(products.status,'available','unavailable')");
                return $products->paginate();
            }

        }

        $accessCompanyId = [];

        $cities = auth('mobile')->user()->Cities->pluck('id')->all();
        $userCategories = auth('mobile')->user()->Categories->pluck('id')->all();
       // $promotions_id = $this->getPromotion();

        $companyId = request('company_id');

        /** @var Role[] $roles */
        if ($companyId) {
            if (!$this->chAc($companyId)) {
                $accessCompanyId[] = $companyId;
            }
        } else {
            //
            //            $userRoles = auth('mobile')->user()->roles;
            //
            //            $roles = Role::with('permissions')
            //                ->where('name', 'like', '%default');
            ////                ->where('kind', 'customer_api');
            //            if (!empty($userRoles)) {
            //                $roles->whereNotIn('company_id', $userRoles->pluck('company_id')->toArray());
            //            }
            //            $roles = $roles->get();
            //
            //            if (!empty($userRoles->toArray())) {
            //
            //                $roles = $roles->merge($userRoles);
            //
            //            }
        }

        if (!empty(auth('mobile')->user()['company_id'])) {
            $accessCompanyId[] = auth('mobile')->user()['company_id'];
        }


        $categoryIds = request('category_ids');
        $brandIds = request('brand_ids');

        $searchName = request('search_name');
        $barcode = request('barcode');

        $hasPromotions = request('has_promotions');

        $userPriceClassIds = auth('mobile')->user()->PriceClasses->pluck('id');

        /** @var Product $products */
        $products = Product::Active()
            ->withCount('Visit')
            ->with([
                'brand',
                'category',
                'photo',
                'photos',
                'company',
                'labels',
                'MasterUnit',
                'SlaveUnit',
                // 'RewardProduct' => function ($q)  use ($promotions_id) {
                //     return $q->with([
                //         'Reward' => function ($qr) use ($promotions_id) {
                //             $qr->with(['promotion'])->select(['id', 'promotion_id']);
                //         }
                //     ])->whereHas('Reward', function ($qr) use ($promotions_id) {
                //         $qr->with('promotion')->whereHas('promotion')->whereIn('promotion_id', $promotions_id);
                //     });
                // },
                // 'RewardBrand' => function ($q) use ($promotions_id) {
                //     return $q->with([
                //         'Reward' => function ($qr) use ($promotions_id) {
                //             $qr->with(['promotion'])->select(['id', 'promotion_id']);
                //         }
                //     ])->whereHas('Reward', function ($qr) use ($promotions_id) {
                //         $qr->with('promotion')->whereHas('promotion')->whereIn('promotion_id', $promotions_id);
                //     });
                // },
                // 'RewardCategory' => function ($q) use ($promotions_id) {
                //     return $q->with([
                //         'Reward' => function ($qr) use ($promotions_id) {
                //             $qr->with(['promotion'])->select(['id', 'promotion_id']);
                //         }
                //     ])->whereHas('Reward', function ($qr) use ($promotions_id) {
                //         $qr->with('promotion')->whereHas('promotion')->whereIn('promotion_id', $promotions_id);
                //     });
                // },
                // 'Slave2Unit',
                // 'PriceClasses' => function ($query) use ($userPriceClassIds) {
                //     $query->whereIn('id', $userPriceClassIds);
                // },
                // 'PriceClasses.Customers' => function ($query) {
                //     $query->where('id', auth()->id());
                // },
            ]);

        if (count($accessCompanyId)) {
            $products = $products->whereHas('company', function ($query) use ($cities, $accessCompanyId) {
                $query->Active()->whereIn('id', $accessCompanyId);
            });
        }
        if (request('serial')) {
            $products->where('serial', request('serial'));
        }

        // if (request('has_promotions')) {
        //     $products->where(function ($query) {
        //         $query->whereHas('RewardProduct')->orwhereHas('RewardBrand')->orwhereHas('RewardCategory');
        //     });
        // }



        $products = $products->orderByRaw("FIELD(products.status,'available','unavailable')");

        $products = $products->CompanyId($companyId)
            ->CategoryIds($categoryIds)
            ->SearchBarcode($barcode)
            ->SearchName($searchName)
            ->SearchBrandByIds($brandIds)
            ->UserCategoryIds($userCategories);

        if ($companyId)
            $products = $products->Order('referral_id',);
        else
            $products = $products->Order(request('order', 'profit'));

        // $products = $products->limit(500)->paginate();
        $products = $products->limit(500)->paginate();

        // foreach ($products as $product) {
        //     $promotions_id_all = array();
        //     $promotios_all = array();
        // //     foreach ($product->RewardProduct as $obj_RewardProduct) {
        //         if (!in_array($obj_RewardProduct['reward']['promotion']['id'], $promotions_id_all)) {
        //             array_push($promotions_id_all, $obj_RewardProduct['reward']['promotion']['id']);
        //             // $promotios_all[] = $obj_RewardProduct['reward']['promotion']->select(['id', 'title', 'description']);
        //             $promotios_all[] = [
        //                 "id" => $obj_RewardProduct['reward']['promotion']['id'],
        //                 "title" => $obj_RewardProduct['reward']['promotion']['title'],
        //                 "description" => $obj_RewardProduct['reward']['promotion']['description'],
        //             ];
        //         }
        //     }
        //     foreach ($product->RewardBrand as $obj_RewardBrand) {
        //         if (!in_array($obj_RewardBrand['reward']['promotion']['id'], $promotions_id_all)) {
        //             array_push($promotions_id_all, $obj_RewardBrand['reward']['promotion']['id']);
        //             // $promotios_all[] = $obj_RewardBrand['reward']['promotion']->select(['id', 'title', 'description']);
        //             $promotios_all[] = [
        //                 "id" => $obj_RewardBrand['reward']['promotion']['id'],
        //                 "title" => $obj_RewardBrand['reward']['promotion']['title'],
        //                 "description" => $obj_RewardBrand['reward']['promotion']['description'],
        //             ];
        //         }
        //     }
        //     foreach ($product->RewardCategory as $obj_RewardCategory) {
        //         if (!in_array($obj_RewardCategory['reward']['promotion']['id'], $promotions_id_all)) {
        //             array_push($promotions_id_all, $obj_RewardCategory['reward']['promotion']['id']);
        //             //  $promotios_all[] = $obj_RewardCategory['reward']['promotion']->select(['id', 'title', 'description']);
        //             $promotios_all[] = [
        //                 "id" => $obj_RewardCategory['reward']['promotion']['id'],
        //                 "title" => $obj_RewardCategory['reward']['promotion']['title'],
        //                 "description" => $obj_RewardCategory['reward']['promotion']['description'],
        //             ];
        //         }
        //     }
        //     $product['promotios'] = $promotios_all;
        //     $product['reward_product'] = array();
        //     $product['RewardBrand'] = array();
        //     $product['RewardCategory'] = array();
        // }


        return $products;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function jwtshow($id)
    {


        // $userPriceClassIds = auth('mobile')->user()->PriceClasses->pluck('id');
        // $promotions_id = $this->getPromotion();

        $product = Product::withCount('Visit')
            ->with([
                'brand',
                'category',
                'photo',
                'photos',
                'company',
                'labels',
                // 'RewardProduct' => function ($q)  use ($promotions_id) {
                //     return $q->with([
                //         'Reward' => function ($qr) use ($promotions_id) {
                //             $qr->with(['promotion'])->select(['id', 'promotion_id']);
                //         }
                //     ])->whereHas('Reward', function ($qr) use ($promotions_id) {
                //         $qr->with('promotion')->whereHas('promotion')->whereIn('promotion_id', $promotions_id);
                //     });
                // },
                // 'RewardBrand' => function ($q) use ($promotions_id) {
                //     return $q->with([
                //         'Reward' => function ($qr) use ($promotions_id) {
                //             $qr->with(['promotion'])->select(['id', 'promotion_id']);
                //         }
                //     ])->whereHas('Reward', function ($qr) use ($promotions_id) {
                //         $qr->with('promotion')->whereHas('promotion')->whereIn('promotion_id', $promotions_id);
                //     });
                // },
                // 'RewardCategory' => function ($q) use ($promotions_id) {
                //     return $q->with([
                //         'Reward' => function ($qr) use ($promotions_id) {
                //             $qr->with(['promotion'])->select(['id', 'promotion_id']);
                //         }
                //     ])->whereHas('Reward', function ($qr) use ($promotions_id) {
                //         $qr->with('promotion')->whereHas('promotion')->whereIn('promotion_id', $promotions_id);
                //     });
                // },

                'MasterUnit',
                'SlaveUnit',
                'Slave2Unit',
                'type1',
                'type2',

                // 'PriceClasses' => function ($query) use ($userPriceClassIds) {
                //     $query->whereIn('id', $userPriceClassIds);
                // },
                // 'PriceClasses.Customers' => function ($query) {
                //     $query->where('id', auth()->id());
                // },
            ])->findOrFail($id);




        $promotions_id_all = array();
        $promotios_all = array();
        // foreach ($product->RewardProduct as $obj_RewardProduct) {
        //     if (!in_array($obj_RewardProduct['reward']['promotion']['id'], $promotions_id_all)) {
        //         array_push($promotions_id_all, $obj_RewardProduct['reward']['promotion']['id']);
        //         // $promotios_all[] = $obj_RewardProduct['reward']['promotion']->select(['id', 'title', 'description']);
        //         $promotios_all[] = [
        //             "id" => $obj_RewardProduct['reward']['promotion']['id'],
        //             "title" => $obj_RewardProduct['reward']['promotion']['title'],
        //             "description" => $obj_RewardProduct['reward']['promotion']['description'],
        //         ];
        //     }
        // }
        // foreach ($product->RewardBrand as $obj_RewardBrand) {
        //     if (!in_array($obj_RewardBrand['reward']['promotion']['id'], $promotions_id_all)) {
        //         array_push($promotions_id_all, $obj_RewardBrand['reward']['promotion']['id']);
        //         // $promotios_all[] = $obj_RewardBrand['reward']['promotion']->select(['id', 'title', 'description']);
        //         $promotios_all[] = [
        //             "id" => $obj_RewardBrand['reward']['promotion']['id'],
        //             "title" => $obj_RewardBrand['reward']['promotion']['title'],
        //             "description" => $obj_RewardBrand['reward']['promotion']['description'],
        //         ];
        //     }
        // }
        // foreach ($product->RewardCategory as $obj_RewardCategory) {
        //     if (!in_array($obj_RewardCategory['reward']['promotion']['id'], $promotions_id_all)) {
        //         array_push($promotions_id_all, $obj_RewardCategory['reward']['promotion']['id']);
        //         //  $promotios_all[] = $obj_RewardCategory['reward']['promotion']->select(['id', 'title', 'description']);
        //         $promotios_all[] = [
        //             "id" => $obj_RewardCategory['reward']['promotion']['id'],
        //             "title" => $obj_RewardCategory['reward']['promotion']['title'],
        //             "description" => $obj_RewardCategory['reward']['promotion']['description'],
        //         ];
        //     }
        // }
        $product['promotios'] = $promotios_all;
        $product['reward_product'] = array();
        $product['RewardBrand'] = array();
        $product['RewardCategory'] = array();


        return $product;
    }

    public function similar($id, Request $request)
    {
        $checkCompany = true;

        if (isset($request->company) && $request->company == 0) {
            $checkCompany = false;
        }

        /** @var Product $product */
        $product = Product::findOrFail($id);

        /** @var Product[] $similarProducts */
        $similarProducts = Product::Active()
            ->with([
                'photo',
                'photos',
                'promotions' => function ($query) {
                    $query->where('status', Promotions::STATUS_ACTIVE);
                },
                'labels',
            ])
            ->where('category_id', $product->category_id)
            ->where('id', '<>', $id);
        //			->where('brand_id', $product->brand_id)


        if ($checkCompany)
            $similarProducts = $similarProducts->where('company_id', $product->company_id);
        else {
            $cities = auth('mobile')->user()->Cities->pluck('id')->all();

            $similarProducts = $similarProducts->whereHas('company', function ($query) use ($cities) {
                $query->whereCities($cities);
            });
        }


        $similarProducts = $similarProducts->take(4)->get();

        return $similarProducts;
    }

    /**
     * @param ScoreCompanyRequest $request
     * @param                     $id
     *
     * @return array
     */
    public function score(ScoreCompanyRequest $request, $id)
    {
        /** @var Product $product */
        $product = Product::findOrFail($id);

        /** @var Score $score */
        $score = Score::firstOrNew([
            'user_id' => auth()->id(),
            'product_id' => $id,
        ]);
        $score->score = $request->score;
        $score->save();

        $avgScore = Score::where('product_id', '=', $product->id)->avg('score');

        $product->score = $avgScore;
        $product->save();

        return [
            'status' => true,
            'message' => trans('messages.api.customer.product.score', [
                'score' => $score->score,
                'name' => $product->name_fa,
            ]),
            'score' => $avgScore,
        ];
    }

    /**
     * @param                     $id
     *
     * @return array
     */
    public function getScore($id)
    {
        /** @var Product $product */
        $product = Product::findOrFail($id);

        /** @var Score $score */
        $score = Score::where([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ])->first();

        if ($score) {
            return [
                'score' => $score->score,
            ];
        }

        return [
            'score' => 0,
        ];
    }

    public function visit_store(ProductVisitRequest $request)
    {
        $productVisit = ProductVisit::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if (empty($productVisit)) {
            ProductVisit::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id
            ]);
        }

        return [
            'status' => true,
            'message' => 'با موفقیت ثبت شد'
        ];
    }

    private function  getPromotion()
    {

        $user = User::select(['*'])->with(['Categories', 'Provinces', 'Cities', 'Area', 'Route', 'PriceClasses'])->where('id', auth('mobile')->user()->id)->first();

        $cities = $request->Cities;
        $userCategories = $request->Categories;

        $companyId = request('company_id');

   

        if ($request->company_id) {
            $accessCompanyId[] = $request->company_id;
        }


        $categoryIds = request('category_ids');
        $brandIds = request('brand_ids');

        $searchName = request('search_name');
        $barcode = request('barcode');

        $hasPromotions = request('has_promotions');

        $userPriceClassIds = $request->PriceClasses;

        /** @var Product $products */
        $products = Product::Active()
            ->withCount('Visit')
            ->with([
                //                'brand.PromotionBrands.Promotions' => function ($query) {
                ////                    $query->whereIn('id', (new Promotions)->hasAlowed());
                ////                    $query->whereNull('deleted_at');
                ////                },
                ////                'category.PromotionCategories' => function ($query) {
                ////                    $query->whereHas('Promotions', function ($q) {
                ////                        $q->whereIn('id', (new Promotions)->hasAlowed());
                ////                        $q->whereNull('deleted_at');
                ////                        $q->with('category.PromotionCategories.Promotions');
                ////                    });
                ////                },
                'brand',
                'category',
                'photo',
                'photos',
                'company',
                'labels',
                'MasterUnit',
                'SlaveUnit',
                'Slave2Unit',
                'PromotionsBrands' => function ($query) {
                    $query->where('status', Promotions::STATUS_ACTIVE);
                },
                'PromotionsCategory' => function ($query) {
                    $query->where('status', Promotions::STATUS_ACTIVE);
                },
                'promotions' => function ($query) {
                    $query->whereIn('id', (new Promotions)->hasAlowed());
                },
                // 'PriceClasses' => function ($query) use ($userPriceClassIds) {
                //     $query->whereIn('id', $userPriceClassIds);
                // },
                
            ]);

        if (count($accessCompanyId)) {
            $products = $products->whereHas('company', function ($query) use ($cities, $accessCompanyId) {
                $query->Active()->whereIn('id', $accessCompanyId);
            });
        }
        if(request('serial')){
            $products->where('serial',request('serial') );
        }


        $products = $products->orderByRaw("FIELD(products.status,'available','unavailable')");
       $products = $products->CompanyId($companyId)
            ->CategoryIds($categoryIds)
            ->SearchBarcode($barcode)
            ->SearchName($searchName)
            ->SearchBrandByIds($brandIds)
            ->UserCategoryIds($userCategories);
        if ($hasPromotions)
            $products = $products->hasPromotions();

        if ($companyId)
            $products = $products->Order('referral_id');
        else
            $products = $products->Order(request('order', 'profit'));

        $products = $products->limit(500)->paginate(16);
      //  $products = $this->filterPromotionCategoryBrand($products);

        foreach ($products as $product) {
            $promotions_all = array();

            foreach ($product->PromotionsBrands as $PromotionsBrand) {
                array_push($promotions_all, $PromotionsBrand);
            }
            foreach ($product->PromotionsCategory as $PromotionsCategory) {
                array_push($promotions_all, $PromotionsCategory);
            }
            foreach ($product->promotions as $promotion) {
                array_push($promotions_all, $promotion);
            }
            $product['promotions_all'] = $promotions_all;
        }

        return $products;
    }
}
