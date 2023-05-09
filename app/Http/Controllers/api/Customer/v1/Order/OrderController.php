<?php

namespace App\Http\Controllers\api\Customer\v1\Order;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Order\Order;
use App\Models\Order\Coupon;
use App\Models\Order\Detail;
use Illuminate\Http\Request;
use Core\Packages\order\Visi;
use App\Models\Order\Addition;
use Hekmatinasser\Verta\Verta;
use App\Models\Product\Product;
use App\Models\Setting\Constant;
use App\Events\User\SendSMSEvent;
use App\Models\Product\Promotions;
use App\Events\Order\RegisterOrder;
use App\Models\Order\PaymentMethod;
use function Siler\Functional\even;
use App\Http\Controllers\Controller;
use App\Models\Order\CouponCustomer;
use Core\Packages\customer\Loglogin;
use Illuminate\Database\Eloquent\Model;

use App\Models\Order\OrderCompanyPriorities;
use App\Http\Requests\api\Customer\v1\Order\StoreOrderRequest;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {

        $orders = Order::where('customer_id', auth()->id())
            ->with([
                'company',
                //				'details',
            ])
            ->orderBy('created_at', 'desc');

        if (request('status') && in_array(request('status'), Order::STATUS)) {
            $orders->where('status', request('status'));
        }

        if (request('from')) {
            $from = Verta::parse(request('from'));
            $orders->where('created_at', '>=', $from->formatGregorian('Y-m-d H:i:s'));
        }

        if (request('to')) {
            $to = Verta::parse(request('to'));
            $orders->where('created_at', '<=', $to->formatGregorian('Y-m-d H:i:s'));
        }

        return $orders->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOrderRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrderRequest $request)
    {
        $cities = auth('mobile')->user()->Cities->pluck('id')->all();

        // order by company
        $idOfProducts = collect($request->products)->pluck('id')->all();
        /** @var Product[] $productsEntity */

        $productsEntity = Product::whereIn('id', $idOfProducts)
            ->with([
                'PriceClasses.Customers' => function ($query) {
                    $query->where('id', auth()->id());
                },
            ])
            ->whereHas('company', function ($query) use ($cities) {
                $query->whereCities($cities);
            })
            ->get()->keyBy('id');

        $requestProducts = [];

        foreach ($request->products as $requestProduct) {
            $id = $requestProduct['id'];

            if (!isset($productsEntity[$id]))
                continue;

            $company_id = $productsEntity[$id]->company_id;
            $requestProducts[$company_id][] = $requestProduct;
        }

        // check coupons
        //		$coupons = Coupon::whereIn('coupons', $request->coupons)
        //			->get()
        //			->keyBy('company_id');

        $orderIds = [];

        // store order
        foreach ($requestProducts as $companyId => $products) {
            $paymentMethod = PaymentMethod::where([
                'payment_method_id' => $request->payment_method_id,
                'company_id' => $companyId,
            ])->first();

            $order = new Order();
            $order->status = Order::STATUS_REGISTERED;
            $order->company_id = $companyId;
            $order->imei = $request->imei ?? NULL;

            $order->description = $request->description;
            //			$order->transfer_number = $request->transfer_number;
            //			$order->carriage_fares  = $request->carriage_fares;

            $order->customer_id = auth()->id();
            $order->payment_method_id = $request->payment_method_id;
            if ($paymentMethod) {
                $order->NewPaymentMethod()->associate($paymentMethod);
            }
            $order->amount_promotion = 0;
            $order->date_of_sending = Verta::parse($request->date_of_sending)->DateTime();
            if (!empty($request->registered_source)) {
                $order->registered_source = $request->registered_source;
            }

            if (!empty($request->visitor_id)) {
               $u= Visi::where('user_id',$request->visitor_id)->first();
                $order->visitor_id = $u->id;
            }

            $order->registered_by = auth()->id();
            $order->save();

            $finalPromotions = Promotions::check($products);

            foreach ($products as $product) {

                $productEntity = $productsEntity[$product['id']];

                $detail = new Detail();
                $detail->product_id = $product['id'];
                $detail->unit_price = $productEntity->price;

                list($detail->master, $detail->slave, $detail->slave2) = Product::minimUnit(
                    $product['master'],
                    $product['slave'],
                    $product['slave2'],
                    $productEntity->per_master,
                    $productEntity->per_slave
                );

                $detail->per_master = $productEntity->per_master;
                $detail->per_slave = $productEntity->per_slave;

                $detail->master_unit_id = $productEntity->master_unit_id;
                $detail->slave_unit_id = $productEntity->slave_unit_id;
                $detail->slave2_unit_id = $productEntity->slave2_unit_id;

                $detail->total = Product::calculateTotal(
                    $detail->master,
                    $detail->slave,
                    $detail->slave2,
                    $detail->per_master,
                    $detail->per_slave
                );

                $detail->price = $detail->total * $detail->unit_price;

                /** @var Promotions $finalPromotion */
                if (
                    isset($finalPromotions[Promotions::KIND_PERCENTAGE][$product['id']])
                ) {
                    if ($finalPromotion = $finalPromotions[Promotions::KIND_PERCENTAGE][$product['id']])
                        $detail->discount = ($finalPromotion->discount * $detail->price) / 100;
                }
                $detail->final_price = $detail->price - $detail->discount;

                // Save
                $order->Details()->save($detail);

                // Price & PromotionPrice
                $order->discount += $detail->discount;
                $order->price_without_promotions += $detail->price;
                $order->price_with_promotions += $detail->final_price;

                $order->final_price += $detail->final_price;
            }

            // amount
            foreach ($finalPromotions[Promotions::KIND_AMOUNT] as $finalPromotion) {
                $order->amount_promotion += $finalPromotion->amount;
            }
            $order->final_price -= $order->amount_promotion;


            // baskets
            foreach ($finalPromotions[Promotions::KIND_BASKET] as $finalPromotion) {
                foreach ($finalPromotion->awards as $award) {

                    $detail = new Detail();
                    $detail->product_id = $award->id;
                    $detail->unit_price = $award->price;

                    list($detail->master, $detail->slave, $detail->slave2) = Product::minimUnit(
                        $award->pivot->master,
                        $award->pivot->slave,
                        $award->pivot->slave2,
                        $award->per_master,
                        $award->per_slave
                    );

                    $detail->per_master = $award->per_master;
                    $detail->per_slave = $award->per_slave;

                    $detail->master_unit_id = $award->master_unit_id;
                    $detail->slave_unit_id = $award->slave_unit_id;
                    $detail->slave2_unit_id = $award->slave2_unit_id;

                    $detail->total = Product::calculateTotal(
                        $detail->master,
                        $detail->slave,
                        $detail->slave2,
                        $detail->per_master,
                        $detail->per_slave
                    );

                    $detail->price = $detail->total * $detail->unit_price;
                    $detail->discount = ($award->pivot->discount * $detail->price) / 100;
                    $detail->final_price = $detail->price - $detail->discount;

                    $detail->prise = true;
                    $detail->promotions_id = $finalPromotion->id;

                    // Save
                    $order->Details()->save($detail);

                    // Price & PromotionPrice
                    $order->discount += $detail->discount;
                    $order->price_without_promotions += $detail->price;
                    $order->price_with_promotions += $detail->final_price;

                    $order->final_price += $detail->final_price;
                }
            }

            $finalPriceBeforeCoupon = $order->final_price;

            if ($request->coupons && isset($request->coupons[$companyId])) {
                /** @var Coupon $coupon */
                $coupon = Coupon::isValid(
                    $request->coupons[$companyId],
                    $companyId,
                    auth()->id()
                );

                if ($coupon) {
                    $couponPrice = $coupon->getDiscount($finalPriceBeforeCoupon);

                    $order->discount += $couponPrice;
                    $order->final_price -= $couponPrice;

                    // Coupon
                    $coupon = Coupon::where('coupon', $request->coupons[$companyId])->active()->first();
                    $order->Coupon()->associate($coupon);

                    CouponCustomer::create([
                        'coupon_id' => $coupon->id,
                        'user_id' => $order->customer_id,
                    ]);
                }
            }

            if ($order->NewPaymentMethod) {
                $paymentDiscount = $order->NewPaymentMethod->getDiscount($finalPriceBeforeCoupon);

                $order->discount += $paymentDiscount;
                $order->final_price -= $paymentDiscount;
            }

            $additionPrice = 0;
            if ($request->carriage_fares) {
                $addition = Constant::where('constant_en', 'shippingPrice')->first();

                $additionEntity = new Addition();
                $additionEntity->order_id = $order->id;
                $additionEntity->value = $request->carriage_fares;
                $additionEntity->Key()->associate($addition);
                $additionEntity->save();

                $additionPrice += $additionEntity->value;
            }

            $order->final_price += $additionPrice;
            $order->save();


            $orderIds[] = $order->id;
            $v = verta();
            $msg = "
            سفارش شما به شماره " . $order->id . " مبلغ " . $order->final_price . " در تاریخ " . $v->formatJalaliDate() . "  ثبت شد

              ";
            if (isset(auth('mobile')->user()->mobile_number) && auth('mobile')->user()->mobile_number)
                event(new SendSMSEvent($msg, auth('mobile')->user()->mobile_number));
        }

        if (!empty($request->order_company_priorities)) {

            $orderCompanyPriorities = [];
            foreach (array_unique($request->order_company_priorities) as $index => $orderCompanyPriority) {
                $orderCompanyPriorities[] = [
                    'order_id' => $orderIds[0],
                    'company_id' => $orderCompanyPriority,
                    'priority' => $index + 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
            OrderCompanyPriorities::insert($orderCompanyPriorities);
        }
        event(new RegisterOrder($orderIds));
        return [
            'status' => true,
            'message' => trans('messages.api.customer.order.order.store', ['count' => count($orderIds)]),
            'id' => $orderIds,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order:://where('customer_id', auth()->id())
            with([
                'company',
                'details.product',
                'details.product.PriceClasses.Customers' => function ($query) {
                    //$query->where('id', auth()->id());
                },
                'details.product.photo',
                'details.MasterUnit',
                'details.SlaveUnit',
                'details.Slave2Unit',
                'Additions',
                'Coupon'
            ])
            ->findOrFail($id);

        return $order;
    }

    /**
     * @param Request $request
     *
     * @return array
     */

    public function promotionAmount($finalPromotions, $products, $productIds, $categoryIds)
    {
        $amount_promotion = 0;
        foreach ($finalPromotions[Promotions::KIND_AMOUNT] as $finalPromotion) {
            if (count($finalPromotion->baskets)) {
                $total2 = [];
                $total2_ = [];
                foreach ($finalPromotion->baskets as $baskets) {
                    $total2[] = $baskets->pivot->total;
                    $discount_variables = $baskets->pivot->discount_variables;
                    if ($discount_variables) {
                        $total2_[$baskets->pivot->total] = json_decode($discount_variables)->amount;
                    }
                }

                rsort($total2);

                foreach ($finalPromotion->baskets as $baskets) {
                    foreach ($products as $product) {
                        if ($product['id'] == $baskets->id) {
                            $total = Product::calculateTotal(
                                $product['master'],
                                $product['slave'],
                                $product['slave2'],
                                $baskets->per_master,
                                $baskets->per_slave
                            );
                            break;
                        }
                    }
                }

                foreach ($total2 as $t) {
                    if ($t <= $total) {
                        $amount_promotion += $total2_[$t];
                        break;
                    }
                }
            }

            if (count($finalPromotion->category)) {
                $total2 = [];
                $total2_ = [];
                foreach ($finalPromotion->category as $category) {
                    $total2[] = $category->pivot->total;
                    $total2_[$category->pivot->total] = $category->pivot->amount;
                }

                rsort($total2);


                $sameCategoryProducts = Product::whereIn('id', $productIds)
                    ->get();

                $products = collect($products)->keyBy('id');
                foreach ($sameCategoryProducts as $sameCategoryProduct) {
                  //  if (in_array($category->id, (new Promotions)->parentsCategoory($categoryIds)['nestedCategoryIdsAll'][$sameCategoryProduct->category_id])) {
                        $product = $products[$sameCategoryProduct->id];
                        $total = Product::calculateTotal(
                            $product['master'],
                            $product['slave'],
                            $product['slave2'],
                            $sameCategoryProduct->per_master,
                            $sameCategoryProduct->per_slave
                        );
                    }
               // }

                foreach ($total2 as $t) {
                    if ($t <= $total) {
                        $amount_promotion += $total2_[$t];
                        break;
                    }
                }
            }

            if (count($finalPromotion->brand)) {
                $total2 = [];
                $total2_ = [];
                foreach ($finalPromotion->brand as $brand) {
                    $total2[] = $brand->pivot->total;
                    $total2_[$brand->pivot->total] = $brand->pivot->amount;
                }
                rsort($total2);


                $sameBrandProducts = Product::whereIn('id', $productIds)
                    ->where('brand_id', $brand->id)
                    ->get();

                $products = collect($products)->keyBy('id');

                foreach ($sameBrandProducts as $sameBrandProduct) {
                    $product = $products[$sameBrandProduct->id];
                    $total = Product::calculateTotal(
                        $product['master'],
                        $product['slave'],
                        $product['slave2'],
                        $sameBrandProduct->per_master,
                        $sameBrandProduct->per_slave
                    );
                }
                foreach ($total2 as $t) {
                    if ($t <= $total) {
                        $amount_promotion += (int)$total2_[$t];
                        break;
                    }
                }
            }
        }

        return $amount_promotion;
    }

    public function promotionPercentage($finalPromotions, $products, $product, $productData, $productIds, $categoryIds)
    {
        $discount = 0;
        if ($finalPromotion = $finalPromotions[Promotions::KIND_PERCENTAGE][$product['id']]) {
            if (count($finalPromotion->baskets)) {
                $total2 = [];
                $total2_ = [];
                foreach ($finalPromotion->baskets as $baskets) {
                    if ($product['id'] == $baskets->id) {
                        $total2[] = $baskets->pivot->total;
                        $discount_variables = $baskets->pivot->discount_variables;
                        if ($discount_variables) {
                            $total2_[$baskets->pivot->total] = json_decode($discount_variables)->discount;
                        }
                    }
                }

                rsort($total2);
                foreach ($finalPromotion->baskets as $baskets) {
                    foreach ($products as $product) {
                        if ($product['id'] == $baskets->id) {
                            $total = Product::calculateTotal(
                                $product['master'],
                                $product['slave'],
                                $product['slave2'],
                                $baskets->per_master,
                                $baskets->per_slave
                            );
                            break;
                        }
                    }
                }

                foreach ($total2 as $t) {
                    if ($t <= $total) {
                        $discount = ($total2_[$t] * $productData['price_total']) / 100;
                        break;
                    }
                }
            }

            if (count($finalPromotion->category)) {
                $total2 = [];
                $total2_ = [];
                foreach ($finalPromotion->category as $category) {
                    $total2[] = $category->pivot->total;
                    $total2_[$category->pivot->total] = $category->pivot->discount;
                }

                rsort($total2);

                $sameCategoryProducts = Product::whereIn('id', $productIds)
                    ->get();

                $products = collect($products)->keyBy('id');


                foreach ($sameCategoryProducts as $sameCategoryProduct) {
                    if (in_array($category->id, (new Promotions)->parentsCategoory($categoryIds)['nestedCategoryIdsAll'][$sameCategoryProduct->category_id])) {

                        $product = $products[$sameCategoryProduct->id];
                        $total = Product::calculateTotal(
                            $product['master'],
                            $product['slave'],
                            $product['slave2'],
                            $sameCategoryProduct->per_master,
                            $sameCategoryProduct->per_slave
                        );
                    }
                }

                foreach ($total2 as $t) {
                    if ($t <= $total) {
                        $discount = ($total2_[$t] * $productData['price_total']) / 100;

                        break;
                    }
                }
            }

            if (count($finalPromotion->brand)) {
                $total2 = [];
                $total2_ = [];
                foreach ($finalPromotion->brand as $brand) {
                    $total2[] = $brand->pivot->total;
                    $total2_[$brand->pivot->total] = $brand->pivot->discount;
                }

                rsort($total2);

                $sameBrandProducts = Product::whereIn('id', $productIds)
                    ->where('brand_id', $brand->id)
                    ->get();

                $products = collect($products)->keyBy('id');

                foreach ($sameBrandProducts as $sameBrandProduct) {
                    $product = $products[$sameBrandProduct->id];
                    $total = Product::calculateTotal(
                        $product['master'],
                        $product['slave'],
                        $product['slave2'],
                        $sameBrandProduct->per_master,
                        $sameBrandProduct->per_slave
                    );
                }

                foreach ($total2 as $t) {

                    if ($t <= $total) {

                        $discount = ($total2_[$t] * $productData['price_total']) / 100;
                        break;
                    }
                }
            }
        }
        return $discount;
    }

    public function promotionPercentageStrip(
        $finalPromotions,
        $products,
        $product,
        $productData,
        $idOfProducts,
        $allCategoryCurrentFactor,
        $allBrandCurrentFactor,
        $requestProductsByIds,
        $factor
    ) {
        if (isset($finalPromotions[Promotions::KIND_PERCENTAGE_STRIP]) and count($finalPromotions[Promotions::KIND_PERCENTAGE_STRIP]) > 0) {
            $final_promotion_discount = 0;
            foreach ($finalPromotions[Promotions::KIND_PERCENTAGE_STRIP] as $finalPromotion) {
                if (count($finalPromotion->baskets)) {
                    foreach ($finalPromotion->baskets as $baskets) {
                        $discount_variables = $baskets->pivot->discount_variables;
                        if ($product['id'] == $baskets->id) {
                            if ($discount_variables) {
                                foreach (json_decode($discount_variables) as $discount_variable) {
                                    $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                    $discount_variable_min = (int)str_replace(',', '', $discount_variable->min);
                                    $discount_variable_percent = (int)str_replace(',', '', $discount_variable->percent);
                                    if ($discount_variable_max > $productData['final_price'] and $productData['final_price'] > $discount_variable_min) {
                                        $final_promotion_discount = (($discount_variable_percent * $productData['final_price']) / 100);
                                    } elseif ($discount_variable_max < $productData['final_price']) {
                                        $final_promotion_discount = (($discount_variable_percent * $productData['final_price']) / 100);
                                    }
                                }
                            }
                        }
                    }
                }
                if (count($finalPromotion->category)) {

                    $allCategoryPromotion = [];
                    $total2 = [];
                    $total2_ = [];
                    $total2__ = [];
                    $xBreak = false;
                    foreach ($finalPromotion->category as $category) {
                        $allCategoryPromotion[] = $category->pivot->category_id;
                        if (!in_array($category->pivot->category_id, $allCategoryCurrentFactor)) {
                            $xBreak = true;
                        }
                        $total2[] = $category->pivot->total;
                        $total2_[$category->pivot->total] = $category->pivot->amount;
                        $total2__[] = $category->pivot->discount_variables;
                    }

                    if ($xBreak == false) {

                        $sameCategoryProducts = Product::whereIn('id', $idOfProducts)
                            ->where('category_id', $category->id)
                            ->get();

                        $products = collect($products)->keyBy('id');

                        foreach ($sameCategoryProducts as $sameCategoryProduct) {
                            $product = $requestProductsByIds[$sameCategoryProduct->id];
                            $total = Product::calculateTotal(
                                $product['master'],
                                $product['slave'],
                                $product['slave2'],
                                $sameCategoryProduct->per_master,
                                $sameCategoryProduct->per_slave
                            );
                        }


                        if (max($total2) <= $total) {
                            $discount_variables = $total2__[0];
                            if ($discount_variables) {
                                $maxAmount = [];
                                $maxAmountPercent = [];
                                if (is_array(json_decode($discount_variables))) {
                                    foreach (json_decode($discount_variables) as $discount_variable) {
                                        $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                        $maxAmount[] = $discount_variable_max;
                                        $maxAmountPercent[$discount_variable_max] = $discount_variable->percent;
                                    }

                                    foreach (json_decode($discount_variables) as $discount_variable) {
                                        $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                        $discount_variable_min = (int)str_replace(',', '', $discount_variable->min);
                                        $discount_variable_percent = (int)str_replace(',', '', $discount_variable->percent);
                                        if ($discount_variable_max > $factor['final_price'] and $factor['final_price'] > $discount_variable_min) {
                                            $final_promotion_discount = (($discount_variable_percent * $factor['final_price']) / 100);
                                            break;
                                        } else {
                                            if ($factor['final_price'] > max($maxAmount)) {
                                                $final_promotion_discount = (($maxAmountPercent[max($maxAmount)] * max($maxAmount)) / 100);
                                            }
                                        }
                                    }
                                } else {
                                    $discount_variable=json_decode($discount_variables);
                                    $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                    $maxAmount[] = $discount_variable_max;
                                    $maxAmountPercent[$discount_variable_max] = $discount_variable->percent;


                                    $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                    $discount_variable_min = (int)str_replace(',', '', $discount_variable->min);
                                    $discount_variable_percent = (int)str_replace(',', '', $discount_variable->percent);
                                    if ($discount_variable_max > $factor['final_price'] and $factor['final_price'] > $discount_variable_min) {
                                        $final_promotion_discount = (($discount_variable_percent * $factor['final_price']) / 100);
                                        break;
                                    } else {
                                        if ($factor['final_price'] > max($maxAmount)) {
                                            $final_promotion_discount = (($maxAmountPercent[max($maxAmount)] * max($maxAmount)) / 100);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (count($finalPromotion->brand)) {

                    $allBrandPromotion = [];
                    $total2 = [];
                    $total2_ = [];
                    $total2__ = [];
                    $xBreak = false;
                    foreach ($finalPromotion->brand as $brand) {
                        $allBrandPromotion[] = $brand->pivot->brand_id;
                        if (!in_array($brand->pivot->brand_id, $allBrandCurrentFactor)) {
                            $xBreak = true;
                        }
                        $total2[] = $brand->pivot->total;
                        $total2_[$brand->pivot->total] = $brand->pivot->amount;
                        $total2__[] = $brand->pivot->discount_variables;
                    }

                    if ($xBreak == false) {

                        $sameBrandProducts = Product::whereIn('id', $idOfProducts)
                            ->where('brand_id', $brand->id)
                            ->get();

                        $products = collect($products)->keyBy('id');

                        foreach ($sameBrandProducts as $sameBrandProducts) {
                            $product = $requestProductsByIds[$sameBrandProducts->id];
                            $total = Product::calculateTotal(
                                $product['master'],
                                $product['slave'],
                                $product['slave2'],
                                $sameBrandProducts->per_master,
                                $sameBrandProducts->per_slave
                            );
                        }

                        if (max($total2) <= $total) {
                            $discount_variables = $total2__[0];
                            if ($discount_variables) {
                                $maxAmount = [];
                                $maxAmountPercent = [];
                                foreach (json_decode($discount_variables) as $discount_variable) {
                                    $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                    $maxAmount[] = $discount_variable_max;
                                    $maxAmountPercent[$discount_variable_max] = $discount_variable->percent;
                                }

                                foreach (json_decode($discount_variables) as $discount_variable) {
                                    $discount_variable_max = (int)str_replace(',', '', $discount_variable->max);
                                    $discount_variable_min = (int)str_replace(',', '', $discount_variable->min);
                                    $discount_variable_percent = (int)str_replace(',', '', $discount_variable->percent);
                                    if ($discount_variable_max > $factor['final_price'] and $factor['final_price'] > $discount_variable_min) {
                                        $final_promotion_discount = (($discount_variable_percent * $factor['final_price']) / 100);
                                        break;
                                    } else {
                                        if ($factor['final_price'] > max($maxAmount)) {
                                            $final_promotion_discount = (($maxAmountPercent[max($maxAmount)] * max($maxAmount)) / 100);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $productData['discount'] + $final_promotion_discount;
        }
    }

    public function check(Request $request)
    {
            if (auth()->guest()) {
                $idOfProducts = collect($request->products)->pluck('id')->all();
                $productsEntity = Product::whereIn('id', $idOfProducts)
                ->with([
                    'MasterUnit',
                    'SlaveUnit',
                    'Slave2Unit',
                    'Photo',


                ])

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
                $companies = User::whereIn('id', $companyIds)
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
                    $send_prams_factor = [
                        "price" => $factor['final_price'],
                        "rowProducts" => count($productsData)
                    ];

                    $data_promotions = [
                        "off" => 0,
                        "remain" => $factor['final_price']
                    ];

                    $data_promotions_row = $productData;

                    $promotions_all = $IdpromotiosAllowed = []; //Promotions::_()->getPromotions($send_prams_factor, $request->all());
                    //بررسی همه ی پروموشن ها
                    foreach ($promotions_all as $promotions) {

                        //پروموشن برروی قیمت فاکتور اعمال شود یا برای هر محصول به صورت جدا
                        $data_reward = [
                            "off" => 0,
                            //مبلغ تخفیف روی فاکتور اعمال شود یا باقی مانده از پروموشن قبلی
                            "remain" => ($promotions->operating == Promotions::MAINPRICE) ? $factor['final_price'] : $data_promotions['remain'],
                            //مبلغ تخفیف روی فاکتور اعمال شود یا باقی مانده از جایزه قبلی
                            "remain_award" => ($promotions->operating == Promotions::MAINPRICE) ? $factor['final_price'] : $data_promotions['remain']
                        ];

                        $data_temp_row = ($promotions->operating == Promotions::MAINPRICE) ? $productsData : $data_promotions_row;
                        $data_reward_row =$data_temp_row;


                        foreach ($promotions->Reward as $Reward) {

                            if ($Reward->discount_function == "factor") {
                                $mony_for_calculation = ($Reward->operating == Promotions::MAINPRICE) ? $data_reward['remain'] : $data_reward['remain_award'];
                                //تخفیف روی زیر فاکتور
                                if ($Reward->discount_precent) {            //اگر درصدی باشد

                                    $offer = $mony_for_calculation * $Reward->discount_precent / 100;
                                    $data_reward['off'] = $data_reward['off'] + $offer;
                                    $data_reward['remain_award'] = $data_reward['remain_award'] - $offer;
                                } elseif ($Reward->discount_money) { //تخفیف ریالی
                                    $offer = $Reward->discount_money;
                                    $data_reward['off'] = $data_reward['off'] + $offer;
                                    $data_reward['remain_award'] = $data_reward['remain_award'] - $offer;
                                } else {
                                }
                            } else {

                                if ($Reward->product->count()) { //تحفیف محصول
                                    foreach ($Reward->product as $product) {

                                        if ($product->status_discount == Promotions::ACTIVE) {
                                            foreach ($products_for_calculation as $key => $myproduct) {

                                                if ($product->product_id == $myproduct['item']->id) {


                                                    // dd($product->product_id);
                                                    // if($product->)
                                                    // dd($myproduct['final_price']);
                                                }
                                            }
                                        }
                                    }
                                } elseif ($Reward->brand->count()) {
                                } elseif ($Reward->category->count()) {
                                } else {
                                }
                            }
                        }
                        $data_promotions['off'] =  $data_promotions['off'] + $data_reward['off'];
                        $data_promotions['remain'] = $data_promotions['remain'] - $data_reward['off'];
                    }


                    $factor['discount'] += $data_promotions['off'];
                    $factor['final_price'] -= $data_promotions['off'];
                    $factor['markup_price'] += $data_promotions['off'];

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

        Loglogin::updateOrCreate(
            ["user_id" => auth('mobile')->user()->id],
            [
                "user_id" => auth('mobile')->user()->id,
                "created_at" => now()
            ]
        );

        $userPriceClassIds = auth('mobile')->user()->PriceClasses->pluck('id');

        //get ids promotion for this user
        $cities = auth('mobile')->user()->Cities->pluck('id')->all();
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
                'PriceClasses' => function ($query) use ($userPriceClassIds) {
                    $query->whereIn('id', $userPriceClassIds);
                },
                'PriceClasses.Customers' => function ($query) {
                    $query->where('id', auth()->id());
                },
            ])
            ->whereHas('company', function ($query) use ($cities) {
                //$query->whereCities($cities);
            })
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
        $companies = User::whereIn('id', $companyIds)
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
                if (
                    isset($finalPromotions[Promotions::KIND_PERCENTAGE][$product['id']])
                ) {
                    $productData['discount'] = $this->promotionPercentage($finalPromotions, $products, $product, $productData, $productIds, $categoryIds);
                }

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

            $factor['discount'] = $this->promotionPercentageStrip(
                $finalPromotions,
                $products,
                $product,
                $productData,
                $idOfProducts,
                $allCategoryCurrentFactor,
                $allBrandCurrentFactor,
                $requestProductsByIds,
                $factor
            );
            $companyProductsData['discount'] += $factor['discount'];

            $amount_promotion = $this->promotionAmount($finalPromotions, $products, $productIds, $categoryIds);

            $companyProductsData['discount'] += $amount_promotion;
            $companyProductsData['final_price'] -= $amount_promotion;
            $companyProductsData['markup_price'] += $amount_promotion;
            $factor['discount'] += $amount_promotion;
            $factor['final_price'] -= $amount_promotion;
            $factor['markup_price'] += $amount_promotion;
            $factor['discount'] += $final_discount;
            $companyProductsData['discount'] += $final_discount;
            if ($factor['discount'] > 0) {
                $factor['markup_price'] = $factor['discount'] + $factor['markup_price'];
            }
            if ($companyProductsData['discount'] > 0) {
                $companyProductsData['markup_price'] = $companyProductsData['discount'] + $companyProductsData['markup_price'];
            }

            foreach ($finalPromotions[Promotions::KIND_BASKET] as $finalPromotion) {
                foreach ($finalPromotion->awards as $award) {
                    $productData = [];
                    $productData['total'] = Product::calculateTotal(
                        $award->pivot->master,
                        $award->pivot->slave,
                        $award->pivot->slave2,
                        $award->per_master,
                        $award->per_slave
                    );
                    $productData['price_total'] = $productData['total'] * $award->price;
                    if (!isset($product->id)) {
                        $productData['product'] = $award->pivot['product_id'];
                        $product['product_id'] = $award->pivot['product_id'];
                    } else {
                        $productData['product'] = $product->id;
                    }

                    $productData['discount'] = ($award->pivot->discount * $productData['price_total']) / 100;
                    $productData['final_price'] = $productData['price_total'] - $productData['discount'];
                    $productData['markup_price'] = $award->consumer_price * $productData['total'] - $productData['final_price'];


                    $productsData[] = [
                        'prise' => true,
                        'item' => $award,
                        'amount' => [
                            'total' => $productData['total'],
                            'master_unit' => $award->pivot->master,
                            'slave_unit' => $award->pivot->slave,
                            'slave2_unit' => $award->pivot->slave2,
                        ],
                        'price' => $productData['price_total'],
                        'discount' => $productData['discount'],
                        'final_price' => $productData['final_price'],
                        'markup_price' => $productData['markup_price'],
                    ];

                    // Price & PromotionPrice
                    $companyProductsData['price'] += $productData['price_total'];
                    $companyProductsData['discount'] += $productData['discount'];
                    $companyProductsData['final_price'] += $productData['final_price'];
                    $companyProductsData['markup_price'] += $productData['markup_price'];
                    if ($final_row_discount_percent) {
                        $companyProductsData['discount'] = ($final_row_discount_percent * $factor['price']) / 100;
                        $factor['discount'] = ($final_row_discount_percent * $factor['price']) / 100;
                    }
                    // Price & PromotionPrice
                    $factor['price'] += $productData['price_total'];
                    $factor['discount'] += $productData['discount'];
                    $factor['final_price'] += $productData['final_price'];

                    $factor['markup_price'] += $productData['markup_price'];
                }
            }

            if (isset($finalPromotions[Promotions::KIND_KALAI]) and count($finalPromotions[Promotions::KIND_KALAI]) > 0) {
                $final_promotion_discount = 0;
                $final_all_discount_promotions = 0;
                $total_valid_product_price = 0;
                $i = 1;
                $unit_type = "";
                $valid_product_keys = [];
                $effected_promotion_by_product_id = [];
                $final_products_fee = collect($productsData)->pluck("amount", 'product_id')->all();

                foreach ($finalPromotions[Promotions::KIND_KALAI] as $finalPromotion) {
                    $total_valid_product_unit_amount = 0;
                    $final_discount_promotions = 0;
                    $promotion_products_final_fee = [];
                    foreach ($finalPromotion->baskets as $baskets) {
                        $basket_fee = [];

                        $discount_volumes = $finalPromotion->volumes;
                        if ($discount_volumes) {
                            if (in_array($baskets->id, $productIds) and !array_key_exists($baskets->id, $basket_fee)) {
                                $basket_fee = [$baskets->id => $final_products_fee[$baskets->id]];
                                array_push($promotion_products_final_fee, $basket_fee);
                            }
                        }
                    }
                    $discount_volumes = $finalPromotion->volumes;
                    foreach ($discount_volumes as $discount_volume) {

                        if ($discount_volume->fld1 > 0) {
                            $unit_type = "master_unit";
                        } elseif ($discount_volume->fld2 > 0) {
                            $unit_type = "slave_unit";
                        } elseif ($discount_volume->fld3 > 0) {
                            $unit_type = "slave2_unit";
                        }
                    }
                    foreach ($promotion_products_final_fee as $promotion_product_final_fee) {
                        foreach ($promotion_product_final_fee as $key => $value) {
                            if ($value['master_unit'] > 0 and $unit_type == "master_unit") {
                                $total_valid_product_unit_amount += (int)$value['master_unit'];
                            } elseif ($value['slave_unit'] > 0 and $unit_type == "slave_unit") {

                                $total_valid_product_unit_amount += (int)$value['slave_unit'];
                            } elseif ($value['slave2_unit'] > 0 and $unit_type == "slave2_unit") {
                                $total_valid_product_unit_amount += (int)$value['slave2_unit'];
                            }
                            array_push($valid_product_keys, [$key => $value]);
                        }
                    }

                    $row_product_status = $finalPromotion->row_product_status;
                    if ($discount_volumes) {

                        foreach ($discount_volumes as $discount_key => $discount_volume) {
                            $have_max_key = count($discount_volumes) > ($discount_key + 1);
                            if ($unit_type == "master_unit") {
                                $discount_variable_min = (int)str_replace(',', '', $discount_volume->fld1);
                                $have_max_key ? $discount_variable_max = (int)str_replace(',', '', $discount_volumes[$discount_key + 1]->fld1) : $discount_variable_max = (int)str_replace(',', '', $discount_volumes[$discount_key]->fld1);
                            } elseif ($unit_type == "slave_unit") {
                                $discount_variable_min = (int)str_replace(',', '', $discount_volume->fld2);
                                $have_max_key ? $discount_variable_max = (int)str_replace(',', '', $discount_volumes[$discount_key + 1]->fld2) : $discount_variable_max = (int)str_replace(',', '', $discount_volumes[$discount_key]->fld2);
                            } elseif ($unit_type == "slave2_unit") {
                                $discount_variable_min = (int)str_replace(',', '', $discount_volume->fld3);
                                $have_max_key ? $discount_variable_max = (int)str_replace(',', '', $discount_volumes[$discount_key + 1]->fld3) : $discount_variable_max = (int)str_replace(',', '', $discount_volumes[$discount_key]->fld3);
                            }
                            $discount_variable_percent = (int)str_replace(',', '', $discount_volume->percent);
                            if (($discount_variable_max > $total_valid_product_unit_amount and $total_valid_product_unit_amount >= $discount_variable_min)) {
                                foreach ($valid_product_keys as $valid_product_key) {
                                    $key = array_keys($valid_product_key)[0];
                                    foreach ($productsData as $product_key => $product) {
                                        $discount_by_row = 0;
                                        if ($product['item']['id'] == $key and !in_array($key, $effected_promotion_by_product_id)) {
                                            $discount_by_row = (($discount_variable_percent * $product["final_price"]) / 100);
                                            array_push($effected_promotion_by_product_id, $key);
                                            $productsData[$product_key]["final_price"] = $product["final_price"] - $discount_by_row;
                                            $productsData[$product_key]["discount"] = $product["discount"] + ($discount_by_row);
                                        }
                                    }
                                }

                                $final_discount_promotions = ($discount_variable_percent * $total_valid_product_price) / 100;
                            } elseif ($discount_variable_max <= $total_valid_product_unit_amount and !$have_max_key) {
                                foreach ($valid_product_keys as $valid_product_key) {
                                    $key = array_keys($valid_product_key)[0];
                                    foreach ($productsData as $product_key => $product) {
                                        $discount_by_row = 0;
                                        if ($product['item']['id'] == $key and !in_array($key, $effected_promotion_by_product_id)) {
                                            $discount_by_row = (($discount_variable_percent * $product["final_price"]) / 100);
                                            array_push($effected_promotion_by_product_id, $key);
                                            $productsData[$product_key]["final_price"] = $product["final_price"] - $discount_by_row;
                                            $productsData[$product_key]["discount"] = $product["discount"] + ($discount_by_row);
                                        }
                                    }
                                }
                                $final_discount_promotions = ($discount_variable_percent * $total_valid_product_price) / 100;
                                break;
                            }
                        }
                    }


                    $final_all_discount_promotions = $final_all_discount_promotions + $final_discount_promotions;

                    $i = $i + 1;
                }

                $final_total_promotion_product_price = $final_all_discount_promotions;

                $final_promotion_discount = $final_all_discount_promotions;

                $final_promotion_price = $factor['final_price'] - $final_promotion_discount;

                $factor['discount'] = $final_promotion_discount + $factor['discount'];
                $factor['final_price'] = $final_promotion_price;
                $companyProductsData['final_price'] = $final_promotion_price;
                $companyProductsData['discount'] = $factor['discount'];
                $last_discount_total_product_price = 0;
                foreach ($productsData as $product_key => $product) {
                    $last_discount_total_product_price += $productsData[$product_key]['discount'];
                    //sadra
                    $productsData[$product_key]['discount'] != 0 ? round(((int)($productsData[$product_key]['price'] / $productsData[$product_key]['discount'])), 3) : 0;
                    //endsadra
                    $disscount_array[] = $last_discount_total_product_price;
                }
                $factor['final_price'] = $factor['final_price'] - $last_discount_total_product_price;
                $factor['discount'] = $last_discount_total_product_price;
                $companyProductsData['final_price'] = $factor['final_price'];
                $companyProductsData['discount'] = $factor['discount'];
            }
            if (isset($finalPromotions[Promotions::KIND_VOLUMETRIC]) and count($finalPromotions[Promotions::KIND_VOLUMETRIC]) > 0) {
                $final_promotion_discount = 0;
                $final_all_discount_promotions = 0;
                $total_valid_product_price = 0;
                $i = 1;
                $includeVolumetric = true;
                $final_products_fee = collect($productsData)->pluck("final_price", 'product_id')->all();

                foreach ($finalPromotions[Promotions::KIND_VOLUMETRIC] as $finalPromotion) {
                    $total_valid_product_price = 0;
                    $final_discount_promotions = 0;
                    $promotion_products_final_fee = [];
                    $valid_product_keys = [];

                    foreach ($finalPromotion->baskets as $baskets) {
                        if (!in_array($baskets['id'], $idOfProducts)) {
                            $includeVolumetric = false;
                        }

                        $basket_fee = [];
                        foreach ($products as $product) {
                            if ($product['id'] == $baskets->id) {
                                $total = Product::calculateTotal(
                                    $product['master'],
                                    $product['slave'],
                                    $product['slave2'],
                                    $baskets->per_master,
                                    $baskets->per_slave
                                );

                                if ($total < $baskets['pivot']['total']) {
                                    $includeVolumetric = false;
                                }
                                break;
                            }
                        }


                        $discount_volumes = $finalPromotion->volumes;
                        $row_product_status = $finalPromotion->row_product_status;
                        if ($discount_volumes) {
                            $prs = true;
                            $discount_variables = $baskets->pivot->discount_variables;

                            if (!empty($discount_variables)) {
                                $prs = json_decode($discount_variables)->product_row_status;
                            }
                            foreach ($discount_volumes as $discount_volume) {
                                foreach ($row_product_status as $row_product) {
                                    if ($baskets->id == $row_product->id && $prs) {
                                        if (((int)$row_product->id == 0 or in_array($row_product->id, $productIds)) and $row_product->status == 1 and !array_key_exists($row_product->id, $basket_fee)) {
                                            $basket_fee = [$row_product->id => $final_products_fee[$row_product->id]];
                                            array_push($promotion_products_final_fee, $basket_fee);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    foreach ($finalPromotion->category as $category) {
                        $discount_variables = $category->pivot->discount_variables;
                        $prs = true;
                        if (!empty($discount_variables)) {
                            $prs = json_decode($discount_variables)->product_row_status;
                        }
                        $discount_volumes = $finalPromotion->volumes;
                        $productCategory = [];
                        if ($discount_volumes) {
                            foreach ($products as $product) {
                                $productCategory[] = $productsEntity[$product['id']]['category_id'];
                                if ($category->id == $productsEntity[$product['id']]['category_id'] &&  $prs) {
                                    $basket_fee = [$product['id'] => $final_products_fee[$product['id']]];
                                    array_push($promotion_products_final_fee, $basket_fee);
                                }
                            }
                        }
                        if (!in_array($category['id'], $productCategory)) {
                            $includeVolumetric = false;
                        }
                    }

                    foreach ($finalPromotion->brand as $brand) {
                        $discount_variables = $brand->pivot->discount_variables;
                        $prs = true;
                        if (!empty($discount_variables)) {
                            $prs = json_decode($discount_variables)->product_row_status;
                        }
                        $discount_volumes = $finalPromotion->volumes;
                        $productBrand = [];
                        if ($discount_volumes) {
                            foreach ($products as $product) {
                                $productBrand[] = $productsEntity[$product['id']]['brand_id'];
                                if ($brand->id == $productsEntity[$product['id']]['brand_id'] &&  $prs) {
                                    $basket_fee = [$product['id'] => $final_products_fee[$product['id']]];
                                    array_push($promotion_products_final_fee, $basket_fee);
                                }
                            }
                        }
                        if (!in_array($brand['id'], $productBrand)) {
                            $includeVolumetric = false;
                        }
                    }

                    foreach ($promotion_products_final_fee as $promotion_product_final_fee) {
                        foreach ($promotion_product_final_fee as $key => $value) {
                            array_push($valid_product_keys, [$key => $value]);
                            $total_valid_product_price += (int)$value;
                        }
                    }
                    $discount_volumes = $finalPromotion->volumes;
                    $row_product_status = $finalPromotion->row_product_status;
                    if ($discount_volumes) {
                        $effected_promotion_by_product_id = [];

                        foreach ($discount_volumes as $discount_volume) {
                            $discount_variable_max = (int)str_replace(',', '', $discount_volume->max);
                            $discount_variable_min = (int)str_replace(',', '', $discount_volume->min);
                            $discount_variable_percent = (int)str_replace(',', '', $discount_volume->percent);;

                            if ($discount_variable_max >= $total_valid_product_price and $total_valid_product_price >= $discount_variable_min) {
                                // dd($discount_variable_min,$discount_variable_max, $total_valid_product_price );

                                foreach ($valid_product_keys as $valid_product_key) {
                                    $key = array_keys($valid_product_key)[0];
                                    foreach ($productsData as $product_key => $product) {
                                        $discount_by_row = 0;
                                        if ($product['item']['id'] == $key and !in_array($key, $effected_promotion_by_product_id)) {
                                            $discount_by_row = (($discount_variable_percent * $product["final_price"]) / 100);

                                            array_push($effected_promotion_by_product_id, $key);
                                            $productsData[$product_key]["final_price"] = $product["final_price"] - $discount_by_row;
                                            $productsData[$product_key]["discount"] = $product["discount"] + ($discount_by_row);
                                        }
                                    }
                                }
                                $final_discount_promotions = ($discount_variable_percent * $total_valid_product_price) / 100;
                            } elseif ($discount_variable_max <= $total_valid_product_price) {
                                $final_discount_promotions = ($discount_variable_percent * $total_valid_product_price) / 100;
                            }
                        }
                    }

                    if ($includeVolumetric == true) {
                        $final_all_discount_promotions = $final_all_discount_promotions + $final_discount_promotions;
                    }

                    $i = $i + 1;
                }
                $final_total_promotion_product_price = $final_all_discount_promotions;

                $final_promotion_discount = $final_all_discount_promotions;

                $final_promotion_price = $factor['final_price'] - $final_promotion_discount;

                $factor['discount'] = $final_promotion_discount + $factor['discount'];
                $factor['final_price'] = $final_promotion_price;
                $companyProductsData['final_price'] = $final_promotion_price;
                $companyProductsData['discount'] = $factor['discount'];
            }
            if (isset($finalPromotions[Promotions::KIND_ROW]) and count($finalPromotions[Promotions::KIND_ROW]) > 0) {

                $final_discount_promotions = 0;
                $final_promotion_discount = 0;

                $final_products_fee = collect($productsData)->pluck("final_price", 'product_id')->all();

                foreach ($finalPromotions[Promotions::KIND_ROW] as $finalPromotion) {

                    $count_rows_global = 0;
                    $basket_fee = [];
                    $final_promotion_discount = 0;
                    $final_all_discount_promotions = 0;
                    $total_valid_product_price = 0;
                    $i = 1;
                    $valid_product_keys = [];
                    $total_valid_product_price = 0;
                    $final_discount_promotions = 0;
                    $promotion_products_final_fee = [];
                    $total_factor_price = 0;
                    $effected_promotion_by_product_id = [];
                    foreach ($finalPromotion->baskets as $baskets) {
                        $basket_fee = [];


                        $discount_volumes = $finalPromotion->volumes;
                        $row_product_status = $finalPromotion->row_product_status;
                        if ($discount_volumes) {

                            foreach ($discount_volumes as $discount_volume) {

                                foreach ($row_product_status as $row_product) {
                                    if ($baskets->id == $row_product->id) {
                                        if (((int)$row_product->id == 0 or in_array($row_product->id, $productIds)) and $row_product->status == 1 and !array_key_exists($row_product->id, $basket_fee)) {
                                            $basket_fee = [$row_product->id => $final_products_fee[$row_product->id]];
                                            array_push($promotion_products_final_fee, $basket_fee);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    foreach ($promotion_products_final_fee as $promotion_product_final_fee) {
                        foreach ($promotion_product_final_fee as $key => $value) {
                            array_push($valid_product_keys, [$key => $value]);
                            $total_valid_product_price += (int)$value;
                        }
                    }
                    foreach ($discount_volumes as $discount_volume) {
                        $count_rows = 0;
                        foreach ($row_product_status as $row_product) {
                            if (((int)$row_product->id == 0 or in_array($row_product->id, $productIds)) and !in_array($row_product->id, $basket_fee) and $row_product->status == 1) {
                                $count_rows = $count_rows + 1;
                                $count_rows_global = $count_rows;
                                $basket_fee[] = $row_product->id;

                                $total_factor_price += $final_products_fee[$row_product->id];
                            }
                        }
                    }

                    $discount_volumes = $finalPromotion->volumes;

                    if ($discount_volumes) {
                        foreach ($discount_volumes as $discount_volume) {
                            $discount_variable_max = (int)str_replace(',', '', $discount_volume->max);
                            $discount_variable_min = (int)str_replace(',', '', $discount_volume->min);
                            $discount_variable_percent = (int)str_replace(',', '', $discount_volume->percent);
                            if ($discount_variable_max >= $count_rows_global and $count_rows_global >= $discount_variable_min) {
                                foreach ($valid_product_keys as $valid_product_key) {
                                    $key = array_keys($valid_product_key)[0];
                                    foreach ($productsData as $product_key => $product) {
                                        $discount_by_row = 0;
                                        if ($product['item']['id'] == $key and !in_array($key, $effected_promotion_by_product_id)) {
                                            $discount_by_row = (($discount_variable_percent * $product["final_price"]) / 100);
                                            array_push($effected_promotion_by_product_id, $key);
                                            $productsData[$product_key]["final_price"] = $product["final_price"] - $discount_by_row;
                                            $productsData[$product_key]["discount"] = $product["discount"] + ($discount_by_row);
                                        }
                                    }
                                }
                                $final_discount_promotions = ($discount_variable_percent * $total_factor_price) / 100;
                                break;
                            } elseif ($discount_variable_max <= $count_rows_global) {

                                //                                $final_discount_promotions = ($discount_variable_percent * $total_factor_price) / 100;
                            }
                        }
                    }
                }

                $final_promotion_discount = $final_discount_promotions;

                $final_promotion_price = $factor['final_price'] - $final_promotion_discount;
                $factor['discount'] = $final_promotion_discount + $factor['discount'];
                $factor['final_price'] = $final_promotion_price;
                $companyProductsData['final_price'] = $final_promotion_price;
                $companyProductsData['discount'] = $factor['discount'];
                //                if (count($finalPromotions[Promotions::KIND_VOLUMETRIC]) > 1) {
                //                    $last_discount_total_product_price = 0;
                //                    foreach ($productsData as $product_key => $product) {
                //                        $last_discount_total_product_price += $productsData[$product_key]['discount'];
                //                        $disscount_array [] = $last_discount_total_product_price;
                //                    }
                //                    $factor['final_price'] = $factor['final_price'] - $last_discount_total_product_price;
                //                    $factor['discount'] = $last_discount_total_product_price;
                //                    $companyProductsData['final_price'] = $factor['final_price'];
                //                    $companyProductsData['discount'] = $factor['discount'];
                //                }

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

    public
    function calculatePrise(Request $request)
    {

        // order by company
        $idOfProducts = collect($request->products)->pluck('id')->all();
        /** @var Product[] $productsEntity */
        $productsEntity = Product::whereIn('id', $idOfProducts)->get()->keyBy('id');

        $requestProducts = [];
        foreach ($request->products as $requestProduct) {

            $id = $requestProduct['id'];
            $company_id = $productsEntity[$id]->company_id;

            $requestProducts[$company_id][] = $requestProduct;
        }

        $finalPromotions = [];

        // store order
        foreach ($requestProducts as $companyId => $products) {
            $finalPromotions[$companyId] = Promotions::check($products);
        }

        return $finalPromotions;
    }
}
