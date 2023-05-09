<?php

namespace core\Packages\shop\src\controllers;

use phpseclib\Net\SSH1;
use Core\Packages\gis\City;
use Illuminate\Http\Request;
use Core\Packages\gis\Province;
use Core\Packages\shop\GeoArea;
use Core\Packages\Coupon\Coupon;
use Core\Packages\slider\Slider;
use App\Models\Product\Promotions;
use Core\Packages\product\Product;
use function Siler\Route\resource;
use Illuminate\Support\Facades\DB;
use App\ModelFilters\ProductFilter;
use App\Http\Controllers\Controller;
use Core\Packages\category\Category;
use Core\Packages\shop\User as ShopUser;
use Core\Packages\shop\Brand as ShopBrand;
use Core\Packages\product\src\request\GetSubProductRequest;

class WithOutLoginController extends Controller
{


    //product jwt functions
    public function product_index(Request $request)
    {
            if($request->most_sale){
            $products = product::select(['*',DB::raw("(select SUM(total)  from details where details.product_id=products.id) as total_sale")])//->withCount('details')->orderBy('details_count' , 'desc')
            ->filter($request->all())
            ->paginate(10);
            return $products ;
            }


        $product = Product::with('brand', 'category', 'photo', 'photos', 'company', 'labels', 'MasterUnit', 'SlaveUnit', 'Slave2Unit',)->filter($request->all())->paginate(10);

        return response($product);
    }
    public function getProductSubCategory($category_id)
    {


        $list_category = collect($category_id);
        $list_temp_category = $category_id;
        $status_while = true;
        do {

            $result = Category::whereIn('parent_id', $list_temp_category)->pluck('id');
            if (!$result->isEmpty()) {
                foreach ($result as $re) {
                    $list_category->push($re);
                }
                $list_temp_category = $result;
            } else {
                $status_while = false;
            }
        } while ($status_while);

        return $list_category;
    }
    public function getProductsByIDcategores(GetSubProductRequest $request)
    {


        $list_category = $this->getProductSubCategory($request->category_id);
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }

        if ($request->has('limit')) {
            $limit = $request->get('limit');
        }

        $products = Product::select('products.*')
            ->withCount('Visit')
            ->with([
                'company',
                'brand',
                'category',
                'photos',
                'Type1',
                'Type2',
                'Visit'
            ]);



        // dd($list_category);
        $products->whereIn('products.category_id', $list_category);
        $products = $products->filter($request->all(), ProductFilter::class);


        return $products->get();
    }

    public function getProductsCompany(Request $request)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }


        $products = Product::select('products.*')
            ->withCount('Visit')
            ->with([
                'company',
                'brand',
                'category',
                'photos',
                'Type1',
                'Type2',
                'Visit'
            ]);


        $products->whereIn('company_id', $request->company_id);



        return $products->get();
    }



    public function product_show($id)
    {
        $product = product::with('brand', 'category', 'photo', 'photos', 'company', 'labels')->find($id);
        return response($product);
    }












    //brand jwt functions
    public function brand_index()
    {


        $brands = ShopBrand::with('photo', 'Companies')->get();
        return response(['data' => $brands]);
    }









    //category jwt functions
    public function category_index()
    {

        // $category = Category::all();
        // return response(['data' => $category]);



            $companyIds = ShopUser::Select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $product= Product::whereIn('company_id', $companyIds)
                ->select('category_id')
                ->get();
            $categoryIds = $product->pluck('category_id')->unique();

            $nodes = Category::query()->with('Photo')
                ->withCount([
                    'products' => function ($query) use ($companyIds) {
                        $query->whereIn('company_id', $companyIds)->active();
                    },
                ])
                ->whereIn('id', $categoryIds)
                ->get();


            $ancestors = Category::query()->with('Photo')
                //			->whereNotIn('id', $categoryIds)
                ->whereNested(function ($inner) use ($nodes) {
                    foreach ($nodes as $node) {
                        $inner->orWhere('_lft', '<', $node->getLft())
                            ->where('_rgt', '>', $node->getLft());
                    }
                })
                ->get();

            $tree = $ancestors->merge($nodes)->toTree();

            return response(['data' => $tree]) ;

    }


















    //company jwt functions
    public function company_index(Request $request, $city = '8')
    {

        $companies = ShopUser::with('photo')->whereHas('Cities', function ($query) use ($city) {
            return $query->where('city_id', $city);
        })->get();

        return response(['data' => $companies]);
    }
    public function company_show($id)
    {

        $companies = ShopUser::with('brands', 'photo', "contacts")->whereHas('Cities', function ($query) use ($id) {
            return $query->where('user_id', $id);
        })->first();
        return response($companies);
    }




    public function order_check(Request $request)
    {

        // dd( );
        //    return $products = Product::with('brand' , 'Category' ,'Photo' ,'Photos' ,'Details')->whereIn('id' , collect($request->products)->pluck('id'))->get();

        // $userPriceClassIds = auth('mobile')->user()->PriceClasses->pluck('id');

        //get ids promotion for this user
        // $cities = auth('mobile')->user()->Cities->pluck('id')->all();
        $final_promotion_discount = 0;
        $final_promotion_price = 0;
        $total_factor_final_price = 0;
        // order by company
        $idOfProducts = collect($request->products)->pluck('id')->all();
        /** @var Product[] $productsEntity */
        $productsEntity = Product::whereIn('id', $idOfProducts)
            ->with([
                'MasterUnit',
                'SlaveUnit',
                'Slave2Unit',
                'Photo',
                // 'PriceClasses' => function ($query) use ($userPriceClassIds) {
                //     $query->whereIn('id', $userPriceClassIds);
                // },
                // 'PriceClasses.Customers' => function ($query) {
                //     $query->where('id', auth()->id());
                // },
            ])
            // ->whereHas('company', function ($query) use ($cities) {
            //     //$query->whereCities($cities);
            // })
            ->get()
            ->keyBy('id');
        $requestProducts = [];
        $allCategoryCurrentFactor = [];
        $allBrandCurrentFactor = [];
        $requestProductsByIds = [];
        foreach ($request->products as $requestProduct) {
            $id = $requestProduct['id'];
            if (!isset($productsEntity[$id]))
                continue;
            $requestProductsByIds[$id] = $requestProduct;
            $company_id = $productsEntity[$id]->company_id;
            $allCategoryCurrentFactor[] = $productsEntity[$id]->category_id;
            $allBrandCurrentFactor[] = $productsEntity[$id]->brand_id;
            $requestProducts[$company_id][] = $requestProduct;
        }


        $productEntities = Product::whereIn('id', $idOfProducts)
            ->get();

        $categoryIds = $productEntities->pluck('category_id')
            ->unique()
            ->all();


        $brandIds = $productEntities->pluck('brand_id')
            ->unique()
            ->all();

        $companyIds = array_keys($requestProducts);
        /** @var User $companies */
        $companies = ShopUser::whereIn('id', $companyIds)
            ->with('photo')
            ->get()
            ->keyBy('id');

        $factor = [
            'companies' => [],
            'price' => 0,
            'discount' => 0,
            'final_price' => 0,
            'markup_price' => 0,
        ];
        //sadra
        $has_discount_product_in_factor = false;
        $has_fee_price_product_in_factor = false;
        //endsadra
        $companiesProductsData = [];
        foreach ($requestProducts as $companyId => $products) {
            $products_id_key = collect($products)->keyBy('id');

            $productIds = $products_id_key->pluck('id')->all();
            $final_discount = 0;
            $final_total_promotion_product_price = 0;
            $final_row_discount_percent = 0;
            $productsData = [];

            $companyProductsData = [
                'price' => 0,
                'discount' => 0,
                'coupon_discount' => 0,
                'final_price' => 0,
                'markup_price' => 0,
            ];
            foreach ($products as $i => $product) {

                $productData = $product;
                $productEntity = $productsEntity[$product['id']];
                $productData['total'] = Product::calculateTotal(
                    $product['master'],
                    $product['slave'],
                    $product['slave2'],
                    $productEntity->per_master,
                    $productEntity->per_slave
                );

                $productData['price'] = $productEntity->price;
                $productData['price_total'] = $productData['total'] * $productEntity->price;

                /** @var Promotions $finalPromotion */

                $productData['discount'] = 0;
                //this is for promotions



                //end promotions


                $productData['final_price'] = $productData['price_total'] - $productData['discount'];

                $productData['markup_price'] = $productEntity->consumer_price * $productData['total'] - $productData['final_price'];
                $productData['markup_price'] = $productData['markup_price'] < 0 ? 0 : $productData['markup_price'];
                //ORGINAL
                //                $productsData[] = [
                //                    'item' => $productEntity,
                //                    'amount' => [
                //                        'total' => $productData['total'],
                //                        'master_unit' => $product['master'],
                //                        'slave_unit' => $product['slave'],
                //                        'slave2_unit' => $product['slave2'],
                //                    ],
                //                    'price' => $productData['price_total'],
                //                    'discount' => $productData['discount'],
                //                    'product_id' => $productData['id'],
                //                    'markup_price' => $productData['markup_price'],
                //                    'final_price' => $productData['final_price'],
                //                ];

                //sadra
                $productsData[] = [
                    'item' => $productEntity,
                    'amount' => [
                        'total' => $productData['total'],
                        'master_unit' => $product['master'],
                        'slave_unit' => $product['slave'],
                        'slave2_unit' => $product['slave2'],
                    ],
                    'price' => $productData['price_total'],
                    'discount' => $productData['discount'],
                    'discount_percent' => $productData['discount'] != 0 ? round(((int)($productData['price_total'] / $productData['discount'])), 3) : 0,
                    'product_id' => $productData['id'],
                    'request_discount_percent' => isset($product['discount_percent']) ? $product['discount_percent'] : 0,
                    "request_discount" => isset($product['discount']) ? $product['discount'] : 0,
                    "request_price" => isset($product['price']) ?? $request->price,
                    'markup_price' => $productData['markup_price'],
                    'final_price' => $productData['final_price'],
                    'final_pay_price' => $productData['final_price'],
                ];
                if ((isset($product['discount']) and $product['discount'] !== 0) or (isset($product['discount_percent']) and $product['discount_percent'] !== 0)) {
                    $has_discount_product_in_factor = true;
                }
                if (isset($product['price']) and $product['price'] !== 0) {
                    $has_fee_price_product_in_factor = true;
                }
                //endsadra
                //				// Price & PromotionPrice
                $companyProductsData['price'] += $productData['price_total'];
                $companyProductsData['discount'] += $productData['discount'];
                $companyProductsData['final_price'] += $productData['final_price'];
                $companyProductsData['markup_price'] += $productData['markup_price'];

                // Price & PromotionPrice
                $factor['price'] += $productData['price_total'];
                $factor['discount'] += $productData['discount'];
                $factor['final_price'] += $productData['final_price'];
                $factor['markup_price'] += $productData['markup_price'];
            }


            $companyProductsData['discount'] += $factor['discount'];



            $factor['discount'] += $final_discount;
            $companyProductsData['discount'] += $final_discount;
            if ($factor['discount'] > 0) {
                $factor['markup_price'] = $factor['discount'] + $factor['markup_price'];
            }
            if ($companyProductsData['discount'] > 0) {
                $companyProductsData['markup_price'] = $companyProductsData['discount'] + $companyProductsData['markup_price'];
            }



            //sadra
            if ($has_fee_price_product_in_factor or $has_discount_product_in_factor) {
                $companyProductsData['price'] = 0;
                $companyProductsData['final_price'] = 0;

                foreach ($productsData as $key => $productData) {
                    if ($has_discount_product_in_factor and isset($productData['request_discount']) and $productData['request_discount'] !== 0) {

                        $dss = $productData['request_discount'];


                        $productsData[$key]['discount_percent'] = round(($dss / $productsData[$key]['final_price']) * 100, 3);

                        $productsData[$key]['final_price'] = $productsData[$key]['final_price'] - $dss;
                        $productsData[$key]['final_pay_price'] = $productsData[$key]['final_price'];
                        $productsData[$key]['discount'] = $dss;
                    }
                    if ($has_discount_product_in_factor and isset($productData['request_discount_percent']) and $productData['request_discount_percent'] !== 0) {
                        $dss = $productData['request_discount_percent'];
                        $productsData[$key]['discount'] = round(((int)($productsData[$key]['price'] * $dss) / 100));

                        $productsData[$key]['discount_percent'] = $dss;


                        //                        $productsData[$key]['final_pay_price'] = $productsData[$key]['final_pay_price'] - round(((int)($productsData[$key]['final_pay_price'] * $dss) /100));
                        //                        $productsData[$key]['discount'] = ((int)($productsData[$key]['final_price'] * $dss));
                        $productsData[$key]['final_pay_price'] = $productsData[$key]['final_price'];
                        //                        $productsData[$key]['final_price'] = $productsData[$key]['discount'];
                        //                        $productsData[$key]['discount'] = $productsData[$key]['final_pay_price'];
                        $productsData[$key]['final_price'] = $productsData[$key]['final_price'] - $productsData[$key]['discount'];
                        $productsData[$key]['final_pay_price'] = $productsData[$key]['final_price'];
                    }

                    $companyProductsData['price'] += $productsData[$key]['price'];
                    $factor['price'] = $companyProductsData['price'];

                    $companyProductsData['final_price'] += $productsData[$key]['final_price'];
                    $factor['final_price'] = $companyProductsData['final_price'];
                }
            }
            //endsadra






            //set Promotions
            // $send_prams_factor = [
            //     "price" => $factor['final_price'],
            //     "rowProducts" => count($productsData)
            // ];

            // $data_promotions = [
            //     "off" => 0,
            //     "remain" => $factor['final_price']
            // ];

            // $data_promotions_row = $productData;

            // $promotions_all = $IdpromotiosAllowed = Promotions::_()->getPromotions($send_prams_factor, $request->all());
            // //بررسی همه ی پروموشن ها
            // foreach ($promotions_all as $promotions) {

            //     //پروموشن برروی قیمت فاکتور اعمال شود یا برای هر محصول به صورت جدا
            //     $data_reward = [
            //         "off" => 0,
            //         //مبلغ تخفیف روی فاکتور اعمال شود یا باقی مانده از پروموشن قبلی
            //         "remain" => ($promotions->operating == Promotions::MAINPRICE) ? $factor['final_price'] : $data_promotions['remain'],
            //         //مبلغ تخفیف روی فاکتور اعمال شود یا باقی مانده از جایزه قبلی
            //         "remain_award" => ($promotions->operating == Promotions::MAINPRICE) ? $factor['final_price'] : $data_promotions['remain']
            //     ];

            //     $data_temp_row = ($promotions->operating == Promotions::MAINPRICE) ? $productsData : $data_promotions_row;
            //     $data_reward_row =$data_temp_row;


            //     foreach ($promotions->Reward as $Reward) {

            //         if ($Reward->discount_function == "factor") {
            //             $mony_for_calculation = ($Reward->operating == Promotions::MAINPRICE) ? $data_reward['remain'] : $data_reward['remain_award'];
            //             //تخفیف روی زیر فاکتور
            //             if ($Reward->discount_precent) {            //اگر درصدی باشد

            //                 $offer = $mony_for_calculation * $Reward->discount_precent / 100;
            //                 $data_reward['off'] = $data_reward['off'] + $offer;
            //                 $data_reward['remain_award'] = $data_reward['remain_award'] - $offer;
            //             } elseif ($Reward->discount_money) { //تخفیف ریالی
            //                 $offer = $Reward->discount_money;
            //                 $data_reward['off'] = $data_reward['off'] + $offer;
            //                 $data_reward['remain_award'] = $data_reward['remain_award'] - $offer;
            //             } else {
            //             }
            //         } else {

            //             if ($Reward->product->count()) { //تحفیف محصول
            //                 foreach ($Reward->product as $product) {

            //                     if ($product->status_discount == Promotions::ACTIVE) {
            //                         foreach ($products_for_calculation as $key => $myproduct) {

            //                             if ($product->product_id == $myproduct['item']->id) {


            //                                 // dd($product->product_id);
            //                                 // if($product->)
            //                                 // dd($myproduct['final_price']);
            //                             }
            //                         }
            //                     }
            //                 }
            //             } elseif ($Reward->brand->count()) {
            //             } elseif ($Reward->category->count()) {
            //             } else {
            //             }
            //         }
            //     }
            //     $data_promotions['off'] =  $data_promotions['off'] + $data_reward['off'];
            //     $data_promotions['remain'] = $data_promotions['remain'] - $data_reward['off'];
            // }


            // $factor['discount'] += $data_promotions['off'];
            // $factor['final_price'] -= $data_promotions['off'];
            // $factor['markup_price'] += $data_promotions['off'];

            //end Promotions





            if ($request->coupons && isset($request->coupons[$companyId])) {
                /** @var Coupon $coupon */
                $coupon = Coupon::isValid(
                    $request->coupons[$companyId],
                    $companyId,
                    auth()->id()
                );

                if ($coupon) {
                    $couponPrice = $coupon->getDiscount($companyProductsData['final_price']);

                    //					$companyProductsData['discount']     += $couponPrice;
                    $companyProductsData['coupon_discount'] += $couponPrice;
                    $companyProductsData['final_price'] -= $couponPrice;
                    $companyProductsData['markup_price'] += $couponPrice;

                    $factor['discount'] += $couponPrice;
                    $factor['final_price'] -= $couponPrice;
                    $factor['markup_price'] += $couponPrice;
                }
            }

            //sadra

            if ($request->factor_params and count($request->factor_params) > 0) {
                $total_discount_line = 0;
                $total_price = $companyProductsData['final_price'];
                foreach ($request->factor_params as $param) {
                    if ($param['kind'] == Constant::ADDITIONS and $param['value'] !== 0) {
                        $total_price += $param['value'];
                    }
                    if ($param['kind'] == Constant::DEDUCTIONS and $param['value'] !== 0) {
                        if ($param['value'] > $total_price) {
                            throw new CoreException('مبلغ کسورات نباید کمتر از مبلغ قابل پرداخت باشد');
                        }
                        $total_discount_line += $param['value'];
                        $total_price -= $param['value'];
                    }
                }
                $companyProductsData['final_price'] = $total_price;
                $factor['final_price'] = $total_price;
                $companyProductsData['discount_manual'] = $total_discount_line;
                $factor['discount_manual'] = $total_discount_line;
            }
            //endsadra
            $companiesProductsData[] = [
                'company_id' => $companyId,
                'name_fa' => $companies[$companyId]->name_fa,
                'name_en' => $companies[$companyId]->name_en,
                'lat' => $companies[$companyId]->lat,
                'long' => $companies[$companyId]->long,
                'photo_url' => $companies[$companyId]->photo ? $companies[$companyId]->photo->url : NULL,
                'items' => $productsData,
                'factor' => $companyProductsData,
            ];
        }
        $factor['companies'] = $companiesProductsData;



        return $factor;
    }









    ////common jwt functions
    public function slide_index()
    {
        $slide = Slider::with('File')->where('status', 'active')->get();
        return response($slide);
    }


    public function geo_area($id){

       return  $areas = GeoArea::where('city_id' , $id)->get();
    }
    public function geo_city($id){

         return $cities = City::where('province_id' , $id)->get();
     }
     public function geo_province(){

        return $cities = Province::get();
    }


}
