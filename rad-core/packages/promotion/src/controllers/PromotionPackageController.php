<?php

namespace core\Packages\promotion\src\controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use Core\Packages\product\Product;
use App\ModelFilters\ConstantFilter;
use Core\Packages\category\Category;
use App\ModelFilters\PromotionFilter;
use Core\Packages\promotion\Promotions;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\promotion\src\request\StoreRequest;
use Core\Packages\promotion\src\request\UpdateRequest;
use Core\Packages\promotion\src\request\DestroyRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class PromotionPackageController extends CoreController
{

    private $_fillable = [
        'constant_en',
        'constant_fa',
        'kind',
    ];

    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }

        if ($companyId = request('company_id')) {
            $companyId = request('company_id');
        } else {
            $companyId = auth('api')->user()->company_id;
        }
        $promotions = Promotions::select('promotions.*')
            ->with([
                'company',
                'provinces',
                'cities',
                'areas',
                'routes',
                'customers',
                'PriceClasses',
                'Baskets',
                'Category',
                'Brand'
            ]);

        if ($companyId) {
            $promotions->where('promotions.company_id', $companyId);
        }
        $promotions = $promotions->filter($request->all(), PromotionFilter::class)->jsonPaginate($limit);

        return $promotions;
    }

    public function show($id)
    {
        $result = Promotions::_()->list($id);
        return $this->responseHandler($result);
    }

    public function storeSadra(StoreRequest $request)
    {
        if ($request->basket_kind == Promotions::BASKET_KIND_PRODUCT and $request->kind !== Promotions::KIND_KALAI and $request->kind !== Promotions::KIND_AMOUNT) {
            foreach ($request->baskets as $basket) {

                if (
                    !$basket['pivot']['master'] &&
                    !$basket['pivot']['slave'] &&
                    !$basket['pivot']['slave2']
                ) {
                    return response([
                        'errors' => [
                            'product' => [
                                'مقدار حداقل یک عدد از محصول باید انتخاب شود'
                            ]
                        ],
                        'message' => 'مقدار حداقل یک عدد از محصول باید انتخاب شود',
                        'status' => false
                    ], 422);
                }
            }
        }

        $promotions = $this->createPromotion($request);
        if ($request->kind == Promotions::KIND_PERCENTAGE) {
            $promotions->discount = $request->discount;
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_AMOUNT) {
            if ($request->basket_kind == 'product') {
                foreach ($request->baskets as $basket) {
                    foreach ($basket['pivot'] as $variable) {
                        if (!isset($variable['amount']) || $variable['amount'] == 0) {
                            return response([
                                'errors' => [
                                    'product' => [
                                        'مقادیر تخفیف متغییر نباید 0 باشد'
                                    ]
                                ],
                                'message' => 'مقادیر تخفیف متغییر نباید 0 باشد',
                                'status' => false
                            ], 422);
                        }
                        if ($variable['master'] == 0 and $variable['slave'] == 0 and $variable['slave2'] == 0) {

                            return response([
                                'errors' => [
                                    'product' => [
                                        'مقادیر واحد ها نباید 0 باشد'
                                    ]
                                ],
                                'message' => 'مقادیر واحد ها نباید 0 باشد',
                                'status' => false
                            ], 422);
                        }
                        $total = Product::calculateTotalById(
                            $basket['id'],
                            $variable['master'],
                            $variable['slave'],
                            $variable['slave2']
                        );

                        $promotions = $this->createPromotion($request);
                        $promotions->amount = $variable['amount'];
                        $promotions->save();

                        $promotions->Baskets()->attach($basket['id'], [
                            'master' => $variable['master'],
                            'slave' => $variable['slave'],
                            'slave2' => $variable['slave2'],
                            'discount_variables' => json_encode($basket['pivot']),
                            'total' => $total,
                        ]);
                    }
                }
            }
        } else if ($request->kind == Promotions::KIND_PERCENTAGE_STRIP) {
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_PERCENTAGE_PRODUCT) {
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_ROW or $request->kind == Promotions::KIND_VOLUMETRIC) {
            $promotions->volumes = json_encode($request->volumes);
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_KALAI) {
            $promotions->volumes = json_encode($request->volumes);
            $promotions->save();
            foreach ($request->awards as $award) {
                $promotions->Awards()->attach($award['id'], [
                    'master' => $award['pivot']['master'],
                    'slave' => $award['pivot']['slave'],
                    'slave2' => $award['pivot']['slave2'],
                    'discount' => $award['pivot']['discount'],
                ]);
            }
        } else if ($request->kind == Promotions::KIND_BASKET) {
            $promotions->save();
            foreach ($request->awards as $award) {
                $promotions->Awards()->attach($award['id'], [
                    'master' => $award['pivot']['master'],
                    'slave' => $award['pivot']['slave'],
                    'slave2' => $award['pivot']['slave2'],
                    'discount' => $award['pivot']['discount'],
                ]);
            }
        }

        if ($promotions->basket_kind == Promotions::BASKET_KIND_PRODUCT and $request->kind !== Promotions::KIND_AMOUNT) {
            $row_product = [];
            foreach ($request->baskets as $basket) {

                $total = Product::calculateTotalById(
                    $basket['id'],
                    $basket['pivot']['master'],
                    $basket['pivot']['slave'],
                    $basket['pivot']['slave2']
                );
                if (isset($basket['product_row_status']) and $basket['product_row_status'] !== null) {
                    array_push($row_product, ['id' => $basket['id'], 'status' => $basket['product_row_status']]);
                }
                if (($request->kind == Promotions::KIND_PERCENTAGE_STRIP) and isset($basket['pivot']['variables']) and count($basket['pivot']['variables']) > 0) {

                    foreach ($basket['pivot']['variables'] as $variable) {

                        if ($variable['min'] == 0 or $variable['max'] == 0 or $variable['percent'] == 0) {
                            return response([
                                'errors' => [
                                    'product' => [
                                        'مقادیر تخفیف متغییر نباید 0 باشد'
                                    ]
                                ],
                                'message' => 'مقادیر تخفیف متغییر نباید 0 باشد',
                                'status' => false
                            ], 422);
                        }
                    }
                    $promotions->Baskets()->attach($basket['id'], [
                        'master' => $basket['pivot']['master'],
                        'slave' => $basket['pivot']['slave'],
                        'slave2' => $basket['pivot']['slave2'],
                        'discount_variables' => json_encode($basket['pivot']['variables']),
                        'total' => $total,
                    ]);
                } else {
                    if (($request->kind == Promotions::KIND_PERCENTAGE_PRODUCT) and isset($basket['pivot']['variables']) and count($basket['pivot']['variables']) > 0) {
                        foreach ($basket['pivot']['variables'] as $index => $variable) {
                            $master = $variable['master'] == 0 ? 1 : $variable['master'];
                            $slave = $variable['slave'] == 0 ? 1 : $variable['slave'];
                            $slave2 = $variable['slave2'] == 0 ? 1 : $variable['slave2'];
                            $basket['pivot']['variables'][$index]['total'] = $master * $slave * $slave2;
                        }
                        $promotions->Baskets()->attach($basket['id'], [
                            'master' => 0,
                            'slave' => 0,
                            'slave2' => 0,
                            'total' => 0,
                            'discount_variables' => json_encode($basket['pivot']['variables']),
                        ]);
                    } else {
                        $promotions->Baskets()->attach($basket['id'], [
                            'master' => $basket['pivot']['master'],
                            'slave' => $basket['pivot']['slave'],
                            'slave2' => $basket['pivot']['slave2'],
                            'total' => $total,
                        ]);
                    }
                }
            }
            if ($request->kind == Promotions::KIND_ROW or $request->kind == Promotions::KIND_VOLUMETRIC) {
                $promotions->row_product_status = json_encode($row_product);
                $promotions->save();
            }
        } else if ($promotions->basket_kind == Promotions::BASKET_KIND_CATEGORY) {
            foreach ($request->baskets as $basket) {
                foreach ($basket['pivot'] as $variable) {

                    if ($variable['master'] == 0 and $variable['slave'] == 0 and $variable['slave2'] == 0) {

                        return response([
                            'errors' => [
                                'product' => [
                                    'مقادیر واحد ها نباید 0 باشد'
                                ]
                            ],
                            'message' => 'مقادیر واحد ها نباید 0 باشد',
                            'status' => false
                        ], 422);
                    }
                    $promotions->Category()->attach($basket['id'], [
                        'master' => $variable['master'],
                        'slave' => $variable['slave'],
                        'slave2' => $variable['slave2']
                    ]);
                }
            }
        } else if ($promotions->basket_kind == Promotions::BASKET_KIND_BRAND) {
            $promotions->Brand()->attach($request->brand['id']);
        }

        return [
            'status' => true,
            'message' => trans('messages.product.promotions.store'),
            'id' => $promotions->id,
        ];
    }

    public function prepareData($type, $request, $id = null)
    {

        if ($type == 'update') {
            $promotions = Promotions::findOrFail($id);
        } else {
            $promotions = new Promotions();
        }

        $promotions->kind = $request->kind;
        $promotions->basket_kind = $request->basket_kind;
        $promotions->title = $request->title;
        $promotions->description = $request->description;
        $promotions->company_id = auth('api')->user()->effective_id;
        if (!empty($request->from_date)) {
            $promotions->from_date = Verta::parse($request->from_date)->DateTime();
            $promotions->to_date = Verta::parse($request->to_date)->DateTime();
        } else {
            $promotions->from_date = Carbon::now()->format('Y-m-d');
            $promotions->to_date = Carbon::now()->addYear(10)->format('Y-m-d');
        }
        $promotions->save();

        if (isset($request->provinces) && count($request->provinces)) {
            $promotions->Provinces()->sync(collect(array_unique($request->provinces))->all());
        }

        if (isset($request->cities) && count($request->cities)) {
            $promotions->Cities()->sync(collect(array_unique($request->cities))->all());
        }

        if (isset($request->areas) && count($request->areas)) {
            $promotions->Areas()->sync(collect(array_unique($request->areas))->all());
        }

        if (isset($request->routes) && count($request->routes)) {
            $promotions->Routes()->sync(collect(array_unique($request->routes))->all());
        }

        if (isset($request->customers) && count($request->customers)) {
            $promotions->Customers()->sync(collect(array_unique($request->customers))->all());
        }

        if (isset($request->price_classes) && count($request->price_classes)) {
            $promotions->PriceClasses()->sync(collect(array_unique($request->price_classes))->all());
        }

        $promotions->Baskets()->detach($promotions->baskets->pluck('id')->all());
        $promotions->Category()->detach($promotions->category->pluck('id')->all());
        $promotions->Brand()->detach($promotions->category->pluck('id')->all());
        $promotions->Awards()->detach($promotions->awards->pluck('id')->all());

        if ($request->kind == Promotions::KIND_PERCENTAGE) {
            if ($request->basket_kind == 'product') {
                $kind = 'baskets';
            }
            if ($request->basket_kind == 'category') {
                $kind = 'category';
            }
            if ($request->basket_kind == 'brand') {
                $kind = 'brands';
            }
            foreach ($request->{$kind} as $row) {
                foreach ($row['pivot'] as $pivot) {
                    if (!isset($pivot['discount']) || $pivot['discount'] == 0) {
                        throw new CoreException('مقادیر تخفیف متغییر نباید 0 باشد');
                    }
                    if ($pivot['master'] == 0 and $pivot['slave'] == 0 and $pivot['slave2'] == 0) {
                        throw new CoreException('مقادیر واحد ها نباید 0 باشد');
                    }
                }

                foreach ($request->{$kind} as $row) {
                    foreach ($row['pivot'] as $pivot) {
                        if ($request->basket_kind == 'product') {
                            $total = Product::calculateTotalById(
                                $row['id'],
                                $pivot['master'],
                                $pivot['slave'],
                                $pivot['slave2']
                            );
                            $promotions->Baskets()->attach($row['id'], [
                                'master' => $pivot['master'],
                                'slave' => $pivot['slave'],
                                'slave2' => $pivot['slave2'],
                                'discount_variables' => json_encode($pivot),
                                'total' => $total,
                            ]);
                        }
                        if ($request->basket_kind == 'category') {
                            $promotions->Category()->attach($row['id'], [
                                'slave2' => $pivot['slave2'],
                                'total' => $pivot['slave2'],
                                'discount' => $pivot['discount'],
                                'show' => 1
                            ]);
                            $list_Scategory = $this->getSubCategory($row['id']);
                            foreach ($list_Scategory as $id) {
                                $promotions->Category()->attach($id, [
                                    'slave2' => $pivot['slave2'],
                                    'total' => $pivot['slave2'],
                                    'discount' => $pivot['discount'],
                                    'show' => 0
                                ]);
                            }
                        }
                        if ($request->basket_kind == 'brand') {
                            $promotions->Brand()->attach($row['id'], [
                                'slave2' => $pivot['slave2'],
                                'total' => $pivot['slave2'],
                                'discount' => $pivot['discount']
                            ]);
                        }
                    }
                }
            }
        } else if ($request->kind == Promotions::KIND_AMOUNT) {
            if ($request->basket_kind == 'product') {
                $kind = 'baskets';
            }
            if ($request->basket_kind == 'category') {
                $kind = 'category';
            }
            if ($request->basket_kind == 'brand') {
                $kind = 'brands';
            }
            foreach ($request->{$kind} as $row) {
                foreach ($row['pivot'] as $pivot) {
                    $amount = (int)str_replace(',', '', $pivot['amount']);
                    if (!isset($amount) || $amount == 0) {
                        throw new CoreException('مقادیر تخفیف متغییر نباید 0 باشد');
                    }
                    if ($pivot['master'] == 0 and $pivot['slave'] == 0 and $pivot['slave2'] == 0) {
                        throw new CoreException('مقادیر واحد ها نباید 0 باشد');
                    }
                }

                foreach ($request->{$kind} as $row) {
                    foreach ($row['pivot'] as $pivot) {
                        if ($request->basket_kind == 'product') {
                            $total = Product::calculateTotalById(
                                $row['id'],
                                $pivot['master'],
                                $pivot['slave'],
                                $pivot['slave2']
                            );
                            $promotions->Baskets()->attach($row['id'], [
                                'master' => $pivot['master'],
                                'slave' => $pivot['slave'],
                                'slave2' => $pivot['slave2'],
                                'discount_variables' => json_encode($pivot),
                                'total' => $total,
                            ]);
                        }
                        if ($request->basket_kind == 'category') {
                            $list_Scategory = $this->getSubCategory($row['id']);
                            $promotions->Category()->attach($row['id'], [
                                'slave2' => $pivot['slave2'],
                                'total' => $pivot['slave2'],
                                'amount' => $pivot['amount'],
                                'show' => 1
                            ]);
                            foreach ($list_Scategory as $id) {
                                $promotions->Category()->attach($id, [
                                    'slave2' => $pivot['slave2'],
                                    'total' => $pivot['slave2'],
                                    'amount' => $pivot['amount'],
                                    'show' => 0
                                ]);
                            }
                        }
                        if ($request->basket_kind == 'brand') {
                            $promotions->Brand()->attach($row['id'], [
                                'slave2' => $pivot['slave2'],
                                'total' => $pivot['slave2'],
                                'amount' => $pivot['amount']
                            ]);
                        }
                    }
                }
            }
        } else  if ($request->kind == Promotions::KIND_PERCENTAGE_STRIP) {
        } else if ($request->kind == Promotions::KIND_ROW or $request->kind == Promotions::KIND_VOLUMETRIC) {
            $promotions->volumes = json_encode($request->volumes);
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_KALAI) {
            $promotions->volumes = json_encode($request->volumes);
            $promotions->save();
            foreach ($request->awards as $award) {
                $promotions->Awards()->attach($award['id'], [
                    'master' => $award['pivot']['master'],
                    'slave' => $award['pivot']['slave'],
                    'slave2' => $award['pivot']['slave2'],
                    'discount' => $award['pivot']['discount'],
                ]);
            }
        } else if ($request->kind == Promotions::KIND_BASKET) {
            foreach ($request->awards as $award) {
                $promotions->Awards()->attach($award['id'], [
                    'master' => $award['pivot']['master'],
                    'slave' => $award['pivot']['slave'],
                    'slave2' => $award['pivot']['slave2'],
                    'discount' => $award['pivot']['discount'],
                ]);
            }
        }

        /////////////////////////BASKET KIND
        if ($promotions->basket_kind == Promotions::BASKET_KIND_PRODUCT) {
            $row_product = [];
            foreach ($request->baskets as $basket) {
                if (isset($basket['product_row_status']) and $basket['product_row_status'] !== null) {
                    array_push($row_product, ['id' => $basket['id'], 'status' => $basket['product_row_status']]);
                }
                if (($request->kind == Promotions::KIND_PERCENTAGE_STRIP) and isset($basket['pivot']['variables']) and count($basket['pivot']['variables']) > 0) {
                    $total = Product::calculateTotalById(
                        $basket['id'],
                        $basket['pivot']['master'],
                        $basket['pivot']['slave'],
                        $basket['pivot']['slave2']
                    );
                    foreach ($basket['pivot']['variables'] as $variable) {

                        if ($variable['min'] == 0 or $variable['max'] == 0 or $variable['percent'] == 0) {
                            throw new CoreException('مقادیر تخفیف متغییر نباید 0 باشد');
                        }
                    }
                    $promotions->Baskets()->attach($basket['id'], [
                        'master' => $basket['pivot']['master'],
                        'slave' => $basket['pivot']['slave'],
                        'slave2' => $basket['pivot']['slave2'],
                        'discount_variables' => json_encode($basket['pivot']['variables']),
                        'total' => $total,
                    ]);
                } elseif ($request->kind != Promotions::KIND_AMOUNT and $request->kind != Promotions::KIND_PERCENTAGE) {
                    $total = Product::calculateTotalById(
                        $basket['id'],
                        $basket['pivot']['master'],
                        $basket['pivot']['slave'],
                        $basket['pivot']['slave2']
                    );
                    $promotions->Baskets()->attach($basket['id'], [
                        'master' => $basket['pivot']['master'],
                        'slave' => $basket['pivot']['slave'],
                        'slave2' => $basket['pivot']['slave2'],
                        'total' => $total,
                    ]);
                }
            }
            if ($request->kind == Promotions::KIND_ROW or $request->kind == Promotions::KIND_VOLUMETRIC) {
                $promotions->row_product_status = json_encode($row_product);
                $promotions->save();
            }
        } else if (
            $promotions->basket_kind == Promotions::BASKET_KIND_CATEGORY
            && $request->kind != 'amount' && $request->kind != 'percentage'
        ) {

            if ($request->kind == 'percentage_strip') {
                foreach ($request->category['pivot'] as $pivot) {
                    $promotions->Category()->attach($request->category['id'], [
                        'slave2' => $pivot['slave2'],
                        'total' => $pivot['slave2'],
                        'discount' => $pivot['discount'],
                        'discount_variables' => json_encode($pivot['variables']),
                    ]);
                }
            } elseif ($request->kind == 'volumetric') {
                foreach ($request->category as $category) {
                    $promotions->Category()->attach($category['id'], [
                        'slave2' => $category['pivot']['slave2'],
                        'total' => $category['pivot']['slave2'],
                        'discount_variables' => json_encode($category['pivot']['variables']),
                    ]);
                }
            }
        } else if (
            $promotions->basket_kind == Promotions::BASKET_KIND_BRAND
            && $request->kind != 'amount'
        ) {
            if ($request->kind == 'percentage_strip') {
                foreach ($request->brand['pivot'] as $pivot) {
                    $promotions->Brand()->attach($request->brand['id'], [
                        'slave2' => $pivot['slave2'],
                        'total' => $pivot['slave2'],
                        'discount' => $pivot['discount'],
                        'discount_variables' => json_encode($pivot['variables']),
                    ]);
                }
            } elseif ($request->kind == 'volumetric') {
                foreach ($request->brand as $brand) {
                    $promotions->Brand()->attach($brand['id'], [
                        'slave2' => $brand['pivot']['slave2'],
                        'total' => $brand['pivot']['slave2'],
                        'discount_variables' => json_encode($brand['pivot']['variables']),
                    ]);
                }
            }
        }

        return $promotions->id;
    }

    public function store(StoreRequest $request)
    {
        $result = $this->prepareData('store', $request);

        return [
            'status' => true,
            'message' => trans('messages.product.promotions.store'),
            'id' => $result,
        ];
    }

    public function update(UpdateRequest $request, $id)
    {
        $result = $this->prepareData('update', $request, $id);
        return [
            'status' => true,
            'message' => trans('messages.product.promotions.update'),
            'id' => $result,
        ];
    }

    public function destroy(DestroyRequest $request)
    {
        $result = Promotions::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.product.promotions.destroy'),
        ];
    }

    public function updateSadra(UpdateRequest $request, $id)
    {
        $promotions = Promotions::findOrFail($id);

        if ($request->basket_kind == Promotions::BASKET_KIND_PRODUCT and $request->kind !== Promotions::KIND_KALAI and $request->kind !== Promotions::KIND_AMOUNT) {
            foreach ($request->baskets as $basket) {
                if (
                    !$basket['pivot']['master'] &&
                    !$basket['pivot']['slave'] &&
                    !$basket['pivot']['slave2']
                ) {
                    return response([
                        'errors' => [
                            'product' => [
                                'مقدار حداقل یک عدد از محصول باید انتخاب شود'
                            ]
                        ],
                        'message' => 'مقدار حداقل یک عدد از محصول باید انتخاب شود',
                        'status' => false
                    ], 422);
                }
            }
        }

        $promotions->basket_kind = $request->basket_kind;
        $promotions->title = $request->title;
        $promotions->description = $request->description;
        if ($promotions->kind == Promotions::KIND_PERCENTAGE) {
            $promotions->discount = $request->discount;
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_AMOUNT) {
            $promotions->amount = $request->amount;
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_PERCENTAGE_STRIP) {
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_PERCENTAGE_PRODUCT) {
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_ROW or $request->kind == Promotions::KIND_VOLUMETRIC) {
            $promotions->volumes = json_encode($request->volumes);
            $promotions->save();
        } else if ($request->kind == Promotions::KIND_KALAI) {
            $promotions->volumes = json_encode($request->volumes);
            $promotions->save();
            foreach ($request->awards as $award) {
                $promotions->Awards()->attach($award['id'], [
                    'master' => $award['pivot']['master'],
                    'slave' => $award['pivot']['slave'],
                    'slave2' => $award['pivot']['slave2'],
                    'discount' => $award['pivot']['discount'],
                ]);
            }
        } else if ($promotions->kind == Promotions::KIND_BASKET) {

            $promotions->save();

            $promotions->Awards()->detach($promotions->awards->pluck('id')->all());

            foreach ($request->awards as $award) {
                $promotions->Awards()->attach($award['id'], [
                    'master' => $award['pivot']['master'],
                    'slave' => $award['pivot']['slave'],
                    'slave2' => $award['pivot']['slave2'],
                    'discount' => $award['pivot']['discount'],
                ]);
            }
        }

        $promotions->Baskets()->detach($promotions->baskets->pluck('id')->all());
        $promotions->Category()->detach($promotions->category->pluck('id')->all());
        if ($promotions->basket_kind == Promotions::BASKET_KIND_PRODUCT) {
            $row_product = [];
            foreach ($request->baskets as $basket) {
                foreach ($basket['pivot'] as $variable) {
                    $total = Product::calculateTotalById(
                        $basket['id'],
                        $variable['master'],
                        $variable['slave'],
                        $variable['slave2']
                    );
                    if (isset($basket['product_row_status']) and $variable['product_row_status'] !== null) {
                        array_push($row_product, ['id' => $basket['id'], 'status' => $basket['product_row_status']]);
                    }
                    if ($request->kind == Promotions::KIND_ROW or $request->kind == Promotions::KIND_VOLUMETRIC) {
                        $promotions->row_product_status = json_encode($row_product);
                        $promotions->save();
                    }
                    if (($request->kind == Promotions::KIND_PERCENTAGE_STRIP) and isset($basket['pivot']['variables']) and count($basket['pivot']['variables']) > 0) {
                        foreach ($basket['pivot']['variables'] as $variable) {
                            if ($variable['min'] == 0 or $variable['max'] == 0 or $variable['percent'] == 0) {
                                return response([
                                    'errors' => [
                                        'product' => [
                                            'مقادیر تخفیف متغییر نباید 0 باشد'
                                        ]
                                    ],
                                    'message' => 'مقادیر تخفیف متغییر نباید 0 باشد',
                                    'status' => false
                                ], 422);
                            }
                        }
                        $promotions->Baskets()->attach($basket['id'], [
                            'master' => $basket['pivot']['master'],
                            'slave' => $basket['pivot']['slave'],
                            'slave2' => $basket['pivot']['slave2'],
                            'discount_variables' => json_encode($basket['pivot']['variables']),
                            'total' => $total,
                        ]);
                    } else {
                        if (($request->kind == Promotions::KIND_PERCENTAGE_PRODUCT) and isset($basket['pivot']['variables']) and count($basket['pivot']['variables']) > 0) {
                            foreach ($basket['pivot']['variables'] as $index => $variable) {
                                $master = $variable['master'] == 0 ? 1 : $variable['master'];
                                $slave = $variable['slave'] == 0 ? 1 : $variable['slave'];
                                $slave2 = $variable['slave2'] == 0 ? 1 : $variable['slave2'];
                                $basket['pivot']['variables'][$index]['total'] = $master * $slave * $slave2;
                            }
                            $promotions->Baskets()->attach($basket['id'], [
                                'master' => 0,
                                'slave' => 0,
                                'slave2' => 0,
                                'total' => 0,
                                'discount_variables' => json_encode($basket['pivot']['variables']),
                            ]);
                        } else {
                            $promotions->Baskets()->attach($basket['id'], [
                                'master' => $basket['pivot']['master'],
                                'slave' => $basket['pivot']['slave'],
                                'slave2' => $basket['pivot']['slave2'],
                                'total' => $total,
                            ]);
                        }
                    }
                    if (($request->kind == Promotions::KIND_PERCENTAGE_PRODUCT) and isset($basket['pivot']['variables']) and count($basket['pivot']['variables']) > 0) {

                        foreach ($basket['pivot']['variables'] as $index => $variable) {
                            $master = $variable['master'] == 0 ? 1 : $variable['master'];
                            $slave = $variable['slave'] == 0 ? 1 : $variable['slave'];
                            $slave2 = $variable['slave2'] == 0 ? 1 : $variable['slave2'];
                            $basket['pivot']['variables'][$index]['total'] = $master * $slave * $slave2;
                        }
                        $promotions->Baskets()->attach($basket['id'], [
                            'master' => 0,
                            'slave' => 0,
                            'slave2' => 0,
                            'total' => 0,
                            'discount_variables' => json_encode($basket['pivot']['variables']),
                        ]);
                    }
                }
            }
        } else if ($promotions->basket_kind == Promotions::BASKET_KIND_CATEGORY) {
            $promotions->Category()->attach($request->category['id'], [
                'slave2' => $request->category['pivot']['slave2'],
                'total' => $request->category['pivot']['slave2'],
            ]);
        } else if ($promotions->basket_kind == Promotions::BASKET_KIND_BRAND) {
            $promotions->Brand()->attach($request->brand['id']);
        }

        return [
            'status' => true,
            'message' => trans('messages.product.promotions.update'),
            'id' => $promotions->id,
        ];
    }

    public function states()
    {
        $data = [];
        foreach (Constant::_()::CONSTANT_KINDS as $CONSTANT_KIND) {
            $data[] = [
                'name' => $CONSTANT_KIND,
                'title' => trans("translate.setting.constant.$CONSTANT_KIND"),
            ];
        };

        return response()->json(['kinds' => $data]);
    }


    private function getSubCategory($id)
    {
        $all_category_sub = array();
        $hasSubCategory = true;
        $list_category_temp = array($id);
        do {
            foreach ($list_category_temp as $cate) {
                array_push($all_category_sub, $cate);
            }
            $list_category_temp = Category::whereIn('parent_id', $list_category_temp)->get()->pluck('id')->toArray();
            if (empty($list_category_temp)) {
                $hasSubCategory = false;
            }
        } while ($hasSubCategory);
        unset($all_category_sub[0]);

        return $all_category_sub;
    }
}
