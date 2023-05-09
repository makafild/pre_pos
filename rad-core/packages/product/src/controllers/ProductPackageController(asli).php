<?php

namespace Core\Packages\product\src\controllers;


use Psy\Util\Str;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use Core\Packages\order\Detail;
use Core\Packages\common\Constant;
use Core\Packages\product\Barcode;
use Core\Packages\product\Product;
use App\ModelFilters\ProductFilter;
use Maatwebsite\Excel\Facades\Excel;
use Core\System\Export\KalaExportExcel;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\product\src\request\UpdateRequest;
use Core\Packages\product\src\request\StoreProductRequest;
use Core\Packages\product\src\request\DestroyProductRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class ProductPackageController extends CoreController
{

    //NO PAGINATE
    public function all(Request $request)
    {


        $products = Product::select('products.*')
            ->with([
                'company',
                'brand',
                'category',
                'photos',
            ]);

        if (auth('api')->user()->kind == 'company') {
            $products->where('products.company_id', auth('api')->user()->company_id);
        }
        if ($request->company_id)
            $products->where('products.company_id', $request->company_id);

        return $products->filter($request->all(), ProductFilter::class)->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $limit = 10)
    {


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

        if (auth('api')->user()->kind == 'company') {
            $products->where('products.company_id', auth('api')->user()->company_id);
        }
        /* if($request->has('company_name_fa'))
        {
            $products =  $products->WhereNameCompany($request->has('company_name_fa'));
        }*/
        $products = $products->filter($request->all(), ProductFilter::class);

        //dd($products->toSql());

        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $products = $products->get();
        } else {
            $size = (isset($request->page['size'])) ? $request->page['size'] : 10;

            $products = $products->jsonPaginate($size);
        }

        return $products->filter($request->all())->jsonPaginate($limit);
    }

    public function export(Request $request)
    {


        if ($request->ids == "false") {
            $ids = array();
        } else {
            $ids = explode(",", $request->ids);
        }


        //return Excel::download(new KalaExportExcel($request), 'kala.xlsx');

        //fech data from db with releations
        $products = Product::select('products.*')
            ->with([
                'company',
                'brand',
                'category',
                'photos',
                'Type1',
                'Type2'
            ]);

        if (auth('api')->user()->kind == 'company') {
            $products = $products->where('products.company_id', auth('api')->user()->company_id);
        }
        if (count($ids) > 0) {
            $products = $products->whereIds($ids);
        }

        $products = $products->filter($request->all(), ProductFilter::class)->orderBy('created_at', 'desc');


        $results['data'] = array();
        $products = $products->get()->toArray();
        //set tiltle for excel

        //add product to list
        foreach ($products as $product) {

            //fech filed constant_fa;
            $type_1 = array();
            if ($product['type1']) {
                foreach ($product['type1'] as $type) {
                    array_push($type_1, $type['constant_fa']);
                }
            }
            $created_at = new Verta($product['created_at']);
            $updated_at = new Verta($product['updated_at']);

            $results['data'][] = [
                "شناسه" => $product['id'],
                "کدمرجع" => $product['referral_id'],
                "کد سریال" => $product['serial'],
                "نام فارسی" => $product['name_fa'],
                "نام انگلیسی" => $product['name_en'],
                "نام شرکت" => (isset($product['company']['title'])) ? $product['company']['title'] : 'پیدا نشد',
                "برند" => (isset($product['brand']['name_fa'])) ? $product['brand']['name_fa'] : 'پیدا نشد',
                "پدیدآورنده" => $product['creator'],
                "تعداد صفحه" => $product['number_of_page'],
                "مهارت" => implode(',', $type_1),
                "ژانر" => (isset($product['type2']['constant_fa'])) ? $product['type2']['constant_fa'] : "",
                "گروه کالا" => (isset($product['category']['title'])) ? $product['category']['title'] : 'پیدا نشد',
                "قیمت خرید" => $product['markup_price'],
                "قیمت فروش" => $product['price'],
                "قیمت مشتری" => $product['consumer_price'],
                "تعداد کل" => $product['per_master'],
                "تعداد جزء" => $product['per_slave'],
                "امتیاز" => $product['score'],
                "وضعیت" => $product['status_translate'],
                "وضعیت نمایش" => $product['show_status_translate'],
                "تاریخ بروز رسانی" => str_replace('-', '/', $updated_at->formatDate()),
                "تاریخ ایجاد" => str_replace('-', '/', $created_at->formatDate())
            ];
        }
        ini_set('memory_limit', '512M');
        return json_encode($results);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {

        $product = new Product();
        $product->name_fa = $request->name_fa;
        $product->name_en = $request->name_en;
        $product->description = $request->description;

        $product->sublayer_id = $request->sublayer_id;
        $product->per_master = $request->per_master;
        $product->per_slave = $request->per_slave;
        $product->pay_tax = $request->pay_tax;
        $product->quotas_master = $request->quotas_master;
        $product->quotas_slave = $request->quotas_slave;
        $product->quotas_slave2 = $request->quotas_slave2;

        $product->min_quotas_master = $request->min_quotas_master;
        $product->min_quotas_slave = $request->min_quotas_slave;
        $product->min_quotas_slave2 = $request->min_quotas_slave2;

        $product->master_status = $request->master_status;
        $product->slave_status = $request->slave_status;
        $product->slave2_status = $request->slave2_status;

        if ($request->master_unit) {
            $product->master_unit_id = $request->master_unit['id'];
        }
        if ($request->slave_unit) {
            $product->slave_unit_id = $request->slave_unit['id'];
        }
        if ($request->slave2_unit) {
            $product->slave2_unit_id = $request->slave2_unit['id'];
        }
        if ($request->serial) {
            $product->serial = $request->serial;
        }
        if ($request->length) {
            $product->length = $request->length;
        }
        if ($request->width) {
            $product->width = $request->width;
        }
        if ($request->creator) {
            $product->creator = $request->creator;
        }
        if ($request->number_of_page) {
            $product->number_of_page = $request->number_of_page;
        }
        if ($request->isbn) {
            $product->isbn = $request->isbn;
        }
        if ($request->weight) {
            $product->weight = $request->weight;
        }
        if ($request->age_category) {
            $product->age_category = $request->age_category;
        }
        if ($request->product_type_2) {
            $product->product_type_2 = $request->product_type_2;
        }

        $product->sales_price = $request->sales_price;
        $product->purchase_price = $request->purchase_price;
        $product->consumer_price = $request->consumer_price;
        $product->discount = $request->discount;
        $product->order_column = $request->order_column;

        $product->brand_id = $request->brand['id'];
        $product->category_id = $request->category['id'];
        $product->photo_id = $request->photo_id;
        $product->product_id = isset($request->sublayer_id) ? (int)$request->sublayer_id . random_int(0000, 9999) : random_int(1000000000, 9999999999);
        $product->company_id = auth('api')->user()->company_id;
        $product->has_user_category = ($request->customer_category && is_array($request->customer_category) && count($request->customer_category))
            ? true : false;

        $product->save();

        $product->Labels()->sync($request->labels);
        $product->Photos()->sync($request->photo_id);
        if ($request->product_type_1) {
            $product->Type1()->sync($request->product_type_1);
        }
        if ($request->customer_category)
            $product->UserCategories()->sync($request->customer_category);

        // Store Barcodes
        if ($request->barcodes)
            foreach ($request->barcodes as $barcode) {
                $barcodeEntity = new Barcode();
                $barcodeEntity->barcode = $barcode['barcode'] ?? '';
                $barcodeEntity->Product()->associate($product);
                $barcodeEntity->save();
            }

        foreach ($request->price_classes as $price_class) {
            $product->PriceClasses()->attach($price_class['id'], [
                'price' => $price_class['price'],
            ]);
        }

        return [
            'status' => true,
            'message' => trans('messages.product.product.store'),
            'id' => $product->id,
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

        /** @var Product $product */
        $product = Product::withCount('Visit')
            ->with([
                'Brand',
                'Category',
                'UserCategories',
                'Photos',
                'Company',
                'Labels',
                "Sublayer",
                'MasterUnit',
                'SlaveUnit',
                'Slave2Unit',

                'PriceClasses',
                'Barcodes',
                'Type1',
                'Type2',
                'Visit',
            ])
            ->find($id);
        return $product;
    }

    public function update(UpdateRequest $request, $id)
    {
        $product = Product::with('barcodes');

        if ($this->ISCompany())
            $product->where('company_id', $this->ISCompany());


        if (!$product->count()) {
            return [
                'status' => true,
                'message' => trans('شناسه یافت نشد'),
            ];
        }
        $product = $product->where('id',$id)->first();
        $product->name_fa = $request->name_fa;
        $product->name_en = $request->name_en;
        $product->description = $request->description;
        $product->pay_tax = $request->pay_tax;

        $product->per_master = $request->per_master;
        $product->per_slave = $request->per_slave;

        $product->quotas_master = $request->quotas_master;
        $product->quotas_slave = $request->quotas_slave;
        $product->quotas_slave2 = $request->quotas_slave2;


        if ($request->min_quotas_master) {
            $product->min_quotas_master = $request->min_quotas_master;
        }
        if ($request->min_quotas_slave) {
            $product->min_quotas_slave = $request->min_quotas_slave;
        }
        if ($request->min_quotas_slave2) {
            $product->min_quotas_slave2 = $request->min_quotas_slave2;
        }

        $product->master_status = $request->master_status;
        $product->slave_status = $request->slave_status;
        $product->slave2_status = $request->slave2_status;

        if ($request->master_unit) {
            $product->master_unit_id = $request->master_unit['id'];
        }
        if ($request->slave_unit) {
            $product->slave_unit_id = $request->slave_unit['id'];
        }
        if ($request->slave2_unit) {
            $product->slave2_unit_id = $request->slave2_unit['id'];
        }
        if ($request->serial) {
            $product->serial = $request->serial;
        }
        if ($request->length) {
            $product->length = $request->length;
        }
        if ($request->width) {
            $product->width = $request->width;
        }
        if ($request->creator) {
            $product->creator = $request->creator;
        }
        if ($request->number_of_page) {
            $product->number_of_page = $request->number_of_page;
        }
        if ($request->isbn) {
            $product->isbn = $request->isbn;
        }
        if ($request->weight) {
            $product->weight = $request->weight;
        }
        if ($request->age_category) {
            $product->age_category = $request->age_category;
        }
        if ($request->product_type_2) {
            $product->product_type_2 = $request->product_type_2;
        }

        $product->sales_price = $request->sales_price;
        $product->purchase_price = $request->purchase_price;
        $product->consumer_price = $request->consumer_price;
        $product->discount = $request->discount;
        $product->order_column = $request->order_column;
        $product->sublayer_id = $request->sublayer_id;

        $product->brand_id = $request->brand['id'];
        $product->category_id = $request->category['id'];
        $product->photo_id = $request->photo_id ?? null;
        // $product->company_id = auth('api')->user()->company_id;

        $product->has_user_category = isset($request->customer_category) and count($request->customer_category) ? true : false;
        $product->save();

        if ($request->labels) {
            $product->Labels()->sync($request->labels);
        }
        if ($request->product_type_1) {
            $product->Type1()->sync($request->product_type_1);
        }

        $product->Photos()->sync($request->photo_id);
        if (isset($request->customer_category)) {
            $product->UserCategories()->sync($request->customer_category);
        }


        // Remove One to Many Question
        $remainId = collect($request->barcodes)->pluck('id');
        $diffId = $product->barcodes->pluck('id')
            ->diff($remainId)
            ->toArray();
        Barcode::whereIn('id', $diffId)->delete();

        // Store Barcodes
        if (isset($request->barcodes)) {
            foreach ($request->barcodes as $barcode) {
                if (isset($barcode['id']) && $barcode['id'])
                    $barcodeEntity = Barcode::find($barcode['id']);
                else
                    $barcodeEntity = new Barcode();

                $barcodeEntity->barcode = $barcode['barcode'] ?? '';
                $barcodeEntity->Product()->associate($product);
                $barcodeEntity->save();
            }
        }


        $product->PriceClasses()->detach($product->PriceClasses);
        if ($request->price_classes) {
            foreach ($request->price_classes as $price_class) {

                $product->PriceClasses()->attach($price_class['id'], [
                    'price' => $price_class['price'],
                ]);
            }
        }


        return [
            'status' => true,
            'message' => trans('messages.product.product.update'),
            'id' => $product->id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyProductRequest $request
     *
     * @return array
     */
    public function destroy(DestroyProductRequest $request , Product $product)
    {
        $product->secureDelete($request->id , ['Details','UserCategories','ReportsSaleProductRoute','Type1','Type2','Company','Users','MasterUnit','SlaveUnit','Slave2Unit','Labels','Barcodes','Visit','Promotions','PriceClasses','scopeActive','scopeAvailable','scopeCompanyId']);
    }

    public function list(Request $request)
    {

        $products = Product::with([
            'MasterUnit',
            'SlaveUnit',
            'Slave2Unit',
        ])->filter($request->all(), ProductFilter::class);

        if (auth('api')->user()->company_id) {
            $products = $products->where('products.company_id', auth('api')->user()->company_id);
        }
        $products = $products->get();

        return $products;
    }

    public function categories()
    {
        $items = [];
        foreach (request('categories') as $id) {
            $items[] = $id;
        }

        $products = Product::with([
            'brand',
            'category',
            'photo',
            'photos',
            'company',
            'labels',
            'MasterUnit',
            'SlaveUnit',
            'Slave2Unit',
        ])->whereIn('category_id', $items)->get();
        return $products;
    }

    public function brands()
    {
        $items = [];
        $ids = request('brands');
        foreach ($ids as $id) {
            $items[] = $id;
        }

        $products = Product::with([
            'brand',
            'category',
            'photo',
            'photos',
            'company',
            'labels',
            'MasterUnit',
            'SlaveUnit',
            'Slave2Unit',
        ])->whereIn('brand_id', $items)->get();
        return $products;
    }

    public function best_sales(Request $request)
    {
        $companyId = NULL;

        if (auth()->user()->can('superIndex', Product::class)) {
            $companyId = request('company_id');
        } else if (auth()->user()->can('index', Product::class)) {
            $companyId = auth()->user()->company_id;
        } else {
            abort(500);
        }


        $details = Detail::with([
            'product',
            'product.brand',
            'product.company',
        ])->whereHas('Order.Customer.Countries', function ($query) use ($request) {
            if ($request->countries)
                return $query->whereIn('id', $request->countries);
        })
            ->whereHas('Order.Customer.Provinces', function ($query) use ($request) {
                if ($request->provinces)
                    $query->whereIn('id', $request->provinces);
            })
            ->whereHas('Order.Customer.Cities', function ($query) use ($request) {
                if ($request->cities)
                    $query->whereIn('id', $request->cities);
            });

        if ($companyId) {
            $details = $details->whereHas('Order', function ($query) use ($companyId) {
                return $query->where('id', $companyId);
            });
        }

        $totalDetails = clone $details;

        // choose kind
        if ($request->kind == 'count') {
            $totalDetails = $totalDetails->selectRaw('sum(total) as total')
                ->first();

            $details = $details->groupBy('product_id')
                ->selectRaw('sum(total) as total, product_id')
                ->orderBy('total', 'desc');
        } else if ($request->kind == 'price') {
            $totalDetails = $totalDetails->selectRaw('sum(total) as total')
                ->first();

            $details = $details->groupBy('product_id')
                ->selectRaw('sum(final_price) as total, product_id')
                ->orderBy('total', 'desc');
        }
        $details = $details->get();

        $totalOfProducts = $details->pluck('total', 'product_id');

        $products = $details->pluck('product')->keyBy('id');

        foreach ($products as $id => &$product) {
            $product['total'] = $totalOfProducts[$id];
        }


        $datatable = datatables()->collection($products)
            ->addColumn('company_name_fa', function (Product $product) {
                if ($product->company)
                    return $product->company->name_fa;
            })
            ->addColumn('brand_name_fa', function (Product $product) {
                if ($product->brand)
                    return $product->brand->name_fa;
            })
            ->addColumn('total_percent', function (Product $product) use ($totalDetails) {
                return $product['total'] / $totalDetails->total * 100;
            })
            // status
            ->editColumn('status', function (Product $product) {
                return trans("translate.product.product.{$product->status}");
            })
            ->toJson();

        return $datatable;
    }


    public function states()
    {
        $data = [];
        $status = Product::STATUS;
        $status = array_map(function ($sub_status) {
            $sub_status['title'] = trans("translate.product.status." . $sub_status["value"]);
            return $sub_status;
        }, $status);
        $show_status = Product::SHOW_STATUS;
        $show_status = array_map(function ($sub_status) {
            $sub_status['title'] = trans("translate.product.show_status." . $sub_status["value"]);
            return $sub_status;
        }, $show_status);


        return response()->json(['status' => $status, 'show_status' => $show_status]);
    }


    public function changeStates(Request $request)
    {

        foreach ($request->id as $id) {
            $product = Product::where('id',$id);
            if ($this->ISCompany())
                $product->where('company_id', $this->ISCompany());
                if(!$product->count()) continue;
                $product = $product->first();

            if ($request->type == "status") {
                $product->status = $request->value;
            } elseif ($request->type == "show_status") {
                $product->show_status = $request->value;
            }

            $product->save();
        }
        return [
            'status' => true,
            'message' => trans('messages.product.product.changeStatus'),
        ];
    }

    public function excel()
    {
        ini_set('memory_limit', '4048M');
        ini_set('max_execution_time', '120000');
        $fileName = 'Products-' . Carbon::now()->format("Y-m-d-H-i-s") . '.xlsx';
        return Excel::download(new ProductExport, $fileName);
    }
    private function ISCompany()
    {
        if (auth('api')->user()['kind'] == 'admin')
            return 0;
        else
            return auth('api')->user()->company_id;
    }
}
