<?php

namespace core\Packages\shop\src\controllers;

use DateTime;
use Carbon\Carbon;
use App\Exports\Export;
use Core\Packages\gis\City;
use App\Models\Order\Coupon;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Core\Packages\order\Visi;
use Core\Packages\user\Users;
use App\Models\Order\Addition;
use Core\Packages\order\Order;
use Hekmatinasser\Verta\Verta;
use App\Events\orderAcctivated;
use Core\Packages\order\Detail;
use Core\Packages\product\Brand;
use App\Events\User\SendSMSEvent;
use App\ModelFilters\OrderFilter;
use App\Models\Product\Promotions;
use Core\Packages\common\Constant;
use Core\Packages\product\Product;
use Illuminate\Support\Facades\DB;
use App\Events\Order\RegisterOrder;
use App\Models\Common\Notification;
use App\Models\Order\PaymentMethod;
use Core\Packages\visitor\Visitors;
use Illuminate\Support\Facades\Log;
use App\ModelFilters\ConstantFilter;
use Maatwebsite\Excel\Facades\Excel;
use SebastianBergmann\Type\TypeName;
use App\Models\Order\AdditionInvoice;
use Core\Packages\order\OrderInvoice;
use Core\Packages\order\DetailInvoice;
use App\Events\Order\ChangeStatusEvent;
use Core\System\Export\OrderExportExcel;
use App\Models\Order\Order as OrderOrder;
use App\Models\User\ReasonForNotVisiting;
use Core\System\Exceptions\CoreException;
use phpDocumentor\Reflection\Types\Void_;
// use Core\System\Exceptions\CoreExceptionOk;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\order\src\request\StoreRequest;
use Core\Packages\report\ReportsSaleProductRoute;
use Core\Packages\order\src\request\UpdateRequest;
use Core\Packages\order\src\request\DeliverRequest;
use Core\Packages\order\src\request\DestroyRequest;
use App\Events\Notification\NotificationStoredEvent;
use Core\Packages\order\src\request\PaymentMethodRequest;
use Core\Packages\order\src\request\DeliverInvoiceRequest;
use Core\Packages\order\src\request\DestroyInvoiceRequest;
use Core\Packages\order\src\request\ChangeOrderStatusRequest;
use Core\Packages\order\src\request\PaymentMethodDefaultRequest;

use function Siler\GraphQL\publish;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class OrderShopPackageController extends CoreController
{
    private $_fillable = [
        'payment_method_id',
        'discount',
        'discount_max',
        'constant_en',
        'constant_fa',
        'kind',
    ];




    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if(auth('api')->user()->kind == "superAdmin"){
          return  $orders = Order::select('orders.*')->where('kind' , '1') ->with([
                'PaymentMethod',
                'customer',
                'customer.Provinces',
                'customer.Areas',
                'customer.cities',

                'customer.Addresses',
                'customer.IntroducerCode',
                'company',
                'visitor.User',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])->jsonPaginate($limit);
        }




        if (auth('api')->user()->kind == "consumer") {

            $customerId = request('customer_id');
            $orders = Order::select('orders.*')
            ->CustomerId($customerId)
            ->where('kind' , 'vendor')
            ->where('customer_id',auth('api')->user()->id)

            ->with([
                'PaymentMethod',
                'customer',
                'customer.Provinces',
                'customer.Areas',
                'customer.cities',
                'customer.Addresses',
                'customer.IntroducerCode',
                'company',
                'visitor.User',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])
            ->orderByRaw("FIELD(orders.status,'registered','confirmed','rejected')");
        if (auth('api')->user()->kind == 'company') {
            $orders->where('orders.company_id', auth('api')->user()->company_id);
        }

        $orders = $orders->filter($request->all(), OrderFilter::class)->orderBy($sort, $order);

        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $orders = $orders->get();
        } else {
            $size = (isset($request->page['size'])) ? $request->page['size'] : 10;
            $orders = $orders->jsonPaginate($size);
        }
        return $orders;


        }

        // if ($request->has('sort')) {
        //     $sort_arr = $request->get('sort');
        //     foreach ($sort_arr as $key => $nameSort) {
        //         $request->request->add(["sort" . $key => $nameSort]); //add request
        //     }
        // }

        $customerId = request('customer_id');

        $orders = Order::select('orders.*')
            ->CustomerId($customerId)
            ->where('kind' , '1')
            ->where('company_id',auth('api')->user()->id)

            ->with([
                'PaymentMethod',
                'customer',
                'customer.referrals' => function ($query) {
                    return $query->where('company_id', auth('api')->user()->company_id);
                },
                'customer.Provinces',
                'customer.Areas',
                'customer.cities',
                'customer.Routes',
                'customer.Addresses',
                'customer.IntroducerCode',
                'company',
                'visitor.User',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])
            ->orderByRaw("FIELD(orders.status,'registered','confirmed','rejected')");
        if (auth('api')->user()->kind == 'company') {
            $orders->where('orders.company_id', auth('api')->user()->company_id);
        }

        $orders = $orders->filter($request->all(), OrderFilter::class)->orderBy($sort, $order);

        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $orders = $orders->get();
        } else {
            $size = (isset($request->page['size'])) ? $request->page['size'] : 10;
            $orders = $orders->jsonPaginate($size);
        }
        return $orders;
    }
    public function sing(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        $results = array();

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }


        $orders = Order::select('customer_id', 'created_at', 'id', 'final_price', 'description')
            ->with([
                'Customer' => function ($q) {
                    $q->select('id', 'first_name', 'last_name');
                },
                'Customer.Photo'
            ]);
        if (auth('api')->user()->kind == 'company') {
            $orders->where('orders.company_id', auth('api')->user()->company_id);
        }
        $orders->where('status', 'registered');
        $orders = $orders->orderBy('created_at', 'DESC')->get()->toArray();
        foreach ($orders as $order) {
            $date_create = new Verta($order['created_at']);
            $photo = Detail::with('Product.photo')->where('order_id', $order['id'])->orderBy('total', 'DESC')->first();
            if (isset($photo['product']['photo']))
                $photo = $photo->toArray();

            $results[] = [
                "created_at" => str_replace('-', '/', $date_create->formatDate()),
                "id" => $order['id'],
                "final_price" => $order['final_price'],
                "customer" => $order['customer'],
                "description" => $order['description'],
                "product_photo" => (isset($photo['product']['photo'])) ? $photo['product']['photo'] : '',
            ];
        }
        return $results;
    }

    public function export(Request $request)
    {
        // return Excel::download(new OrderExportExcel($request), 'kala.xlsx');

        if ($request->ids == "false") {
            $ids = array();
        } else {
            $ids = explode(",", $request->ids);
        }


        $customerId = request('customer_id');
        $orders = Order::select('orders.*')
            ->CustomerId($customerId)
            ->with([
                'PaymentMethod',
                'customer',
                'customer.referrals' => function ($query) {
                    return $query->where('company_id', auth('api')->user()->company_id);
                },
                'customer.cities',
                'customer.IntroducerCode',
                'company',
                'visitor.User',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])
            ->orderByRaw("FIELD(orders.status,'registered','confirmed','rejected')");

        if (count($ids) > 0) {
            $orders = $orders->whereIds($ids);
        }

        if (auth('api')->user()->kind == 'company') {
            $orders->where('orders.company_id', auth('api')->user()->company_id);
        }

        $orders = $orders->filter($request->all(), OrderFilter::class)->orderBy('created_at', 'desc');


        $results['data'] = array();
        $orders = $orders->get()->toArray();


        //add product to list
        foreach ($orders as $order) {
            $brands = array();
            foreach ($order['details'] as $details) {
                array_push($brands, $details['product']['brand']['name_fa']);
            }
            $text_order = "";

            if ($order['status'] == "registered")
                $text_order = "ثبت شده";
            elseif ($order['status'] == "confirmed")
                $text_order = "تائید شده";
            else
                $text_order = "رد شده";


            $date_create = new Verta($order['created_at']);
            $date_reference_date = new Verta($order['reference_date']);
            $change_status_date = new Verta($order['change_status_date']);

            $results['data'][] = [
                "شناسه" => $order['id'],
                "کدمرجع" => $order['referral_id'],
                "نام شرکت" => $order['company']['name_fa'],
                "شماره مشتری" => $order['customer_id'],
                "نام مشتری" => $order['customer']['full_name'],
                "نام ویزیتور" => (isset($order['visitor']['user']['full_name'])) ? $order['visitor']['user']['full_name'] : "",
                "موبایل" => $order['customer']['mobile_number'],
                "کد معروف" => $order['customer']['introducer_code_id'],
                "منبع ورود سفارش" => $order['registered_source'],
                "اولویت اول سفارش" => (isset($order['order_company_priorities'][0]['company']['name_fa'])) ? $order['order_company_priorities'][0]['company']['name_fa'] : '',
                "برند" => implode(',', $brands),
                "مبلغ" => $order['final_price'],
                "تایید پرداخت" => $order['payment_confirm'],
                "شماره حواله" => $order['transfer_number'],
                "شناسه دستگاه مشتری" => $order['imei'],
                "دلیل رد سفارش" => (isset($order['reject_text']['constant_fa'])) ? $order['reject_text']['constant_fa'] : "",
                "وضعیت" => $text_order,
                "تاریخ ارسال درخواستی" => $order['date_of_sending_translate'],
                "تاریخ تغییر وضعیت" => str_replace('-', '/', $change_status_date->formatDate()),
                "تاریخ ارجاع" => str_replace('-', '/', $date_reference_date->formatDate()),
                "تاریخ ایجاد" => str_replace('-', '/', $date_create->formatDate())
            ];
        }
        ini_set('memory_limit', '512M');


        return json_encode($results);
    }


    public function export_invoice(Request $request)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }

        $customerId = request('customer_id');

        $orders = OrderInvoice::select('order_invoices.*')
            ->CustomerId($customerId)
            ->with([
                'PaymentMethod',
                'customer',
                'customer.referrals' => function ($query) {
                    return $query->where('company_id', auth('api')->user()->company_id);
                },
                'customer.cities',
                'customer.IntroducerCode',
                'company',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])
            ->orderByRaw("FIELD(order_invoices.status,'registered','confirmed','rejected')");
        if (auth('api')->user()->kind == 'company') {
            $orders->where('order_invoices.company_id', auth('api')->user()->company_id);
        }
        $orders = $orders->filter($request->all(), OrderFilter::class);

        $results['data'] = array();
        $orders = $orders->get()->toArray();



        //add product to list
        foreach ($orders as $order) {
            $brands = array();
            foreach ($order['details'] as $details) {
                array_push($brands, $details['product']['brand']['name_fa']);
            }
            $text_order = "";

            if ($order['status'] == "registered")
                $text_order = "ثبت شده";
            elseif ($order['status'] == "confirmed")
                $text_order = "تائید شده";
            else
                $text_order = "رد شده";


            $date_create = new Verta($order['created_at']);
            $date_reference_date = new Verta($order['reference_date']);
            $change_status_date = new Verta($order['change_status_date']);

            $results['data'][] = [
                "شناسه" => $order['id'],
                "کدمرجع" => $order['referral_id'],
                "نام شرکت" => $order['company']['name_fa'],
                "شماره مشتری" => $order['customer_id'],
                "نام مشتری" => $order['customer']['full_name'],
                "موبایل" => $order['customer']['mobile_number'],
                "کد معروف" => $order['customer']['introducer_code_id'],
                "منبع ورود سفارش" => $order['registered_source'],
                "اولویت اول سفارش" => (isset($order['order_company_priorities'][0]['company']['name_fa'])) ? $order['order_company_priorities'][0]['company']['name_fa'] : '',
                "برند" => implode(',', $brands),
                "مبلغ" => $order['final_price'],
                "تایید پرداخت" => $order['payment_confirm'],
                "شماره حواله" => $order['transfer_number'],
                "شناسه دستگاه مشتری" => $order['imei'],
                "دلیل رد سفارش" => (isset($order['reject_text']['constant_fa'])) ? $order['reject_text']['constant_fa'] : "",
                "وضعیت" => $text_order,
                "تاریخ ارسال درخواستی" => $order['date_of_sending_translate'],
                "تاریخ ارسال قطعی" => $order['deliver_date'],
                "تاریخ تغییر وضعیت" => str_replace('-', '/', $change_status_date->formatDate()),
                "تاریخ ارجاع" => str_replace('-', '/', $date_reference_date->formatDate()),
                "تاریخ ایجاد" => str_replace('-', '/', $date_create->formatDate())
            ];
        }
        ini_set('memory_limit', '512M');


        return json_encode($results);
    }







    public function invoice_index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {


        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }


        $customerId = request('customer_id');

        $orders = OrderInvoice::select('order_invoices.*')
            ->CustomerId($customerId)
            ->with([
                'PaymentMethod',
                'customer',
                'customer.referrals' => function ($query) {
                    return $query->where('company_id', auth('api')->user()->company_id);
                },
                'customer.cities',
                'customer.IntroducerCode',
                'company',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])

            ->orderByRaw("FIELD(order_invoices.status,'registered','confirmed','rejected')");

        if (auth('api')->user()->kind == 'company') {
            $orders->where('order_invoices.company_id', auth('api')->user()->company_id);
        } else {


            $size = (isset($request->page['size'])) ? $request->page['size'] : 10;


            if ($request->has('sort')) {
                foreach ($sort_arr as $key => $value)
                    $cop = $orders->get();
                // dd($cop->first->customer);
                if ($cop[0]->$key == true && $cop[0]->$key != null) {

                    $orders = $orders->orderBy($key, $value);
                }
                if ($cop[0]->$key != null) {

                    $orders = $orders->first()->company->orderBy($key, $value);
                }
            }
        }
        $orders = $orders->filter($request->all(), OrderFilter::class)->jsonPaginate();
        return $orders;
    }

    public function show($id)
    {
        $order = Order::select('orders.*')
            ->with([
                'customer',
                'visitor.User',
                'customer.categories',
                'customer.addresses',
                'customer.cities',
                'company',
                'PaymentMethod',
                'Coupon',
                'details.MasterUnit',
                'details.SlaveUnit',
                'details.Slave2Unit',
                'details.product',
                'additions.key',
                'OrderCompanyPriorities.company',
                'details.product.brand'
            ]);
        if ($this->ISCompany()) {
            $order->where('company_id', $this->ISCompany())->where('id', $id);
        }
        $order = $order->first();

        if (empty($order)) {
            return [
                'status' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }
        return $order;
    }

    public function invoice_show($id)
    {
        $order = OrderInvoice::select('order_invoices.*')
            ->with([
                'customer',
                'visitor.User',
                'customer.categories',
                'customer.addresses',
                'customer.cities',
                'company',
                'PaymentMethod',
                'Coupon',
                'Details.MasterUnit',
                'Details.SlaveUnit',
                'Details.Slave2Unit',
                'Details.product',
                'OrderCompanyPriorities.company',
                'details.product.brand'
            ]);
        if ($this->ISCompany()) {
            $order->where('company_id', $this->ISCompany());
        }

        $order = $order->find($id);

        if (empty($order)) {
            return [
                'status' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }
        return $order;
    }

    public function multiStore(Request $request){




        if (auth('api')->user()->kind != 'consumer') {
            throw new CoreException('شما اجازه ثبت سفارش ندارید');
        }
        $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');




 $grope_id =time().rand(0,999999);


        foreach($request->details as $detail){

            $order = new order() ;
            $order->status = Order::STATUS_REGISTERED;
            $order->group_id =  $grope_id ;
            $order->company_id = $detail['vendor_id'];
            $order->customer_id = auth('api')->user()->id;
            $order->kind = Order::KIND_VENDOR;
            // $order->address_id = $request->address_id['id'];
            $order->updated_by = auth('api')->user() -> id;
            $order->description = $request->description;
            // $order->customer_id = $request->customer_id;
            $order->payment_method_id = $request->payment_method_id;
            $order->registered_source = $request->registered_source;
            $order->registered_by = auth('api')->user()->company_id;

            $order->save();


            foreach( $detail['products'] as $product){

                // $productEntity = Product::where('id', $product['id'])->first();


                    // dd($product['master']);
                $order_detail = new  Detail() ;

                $order_detail->order_id = $order->id;
                $order_detail->vendor_id =$detail['vendor_id'];
                $order_detail->product_id = $product['id'];
                $order_detail->unit_price = $product['price'] ;
                $order_detail->date_id = $detail['date_id'] ;
                $order_detail->master = $product['master'];
                $order_detail->slave =$product['slave'];
                $order_detail->slave2 = $product['slave2'];
                $order_detail->date_id = $detail['date_id']  ;
                $product_data = Product::where('id' , $product['id'])->first();
                // dd($product_data);
                $order_detail->total = Product::calculateTotal(
                    $product['master'],
                    $product['slave'],
                    $product['slave2'],
                    $product_data->per_master,
                    $product_data->per_slave,
                );
                // dd($product_data);
                $order_detail->master_unit_id = $product_data->master_unit_id;
                $order_detail->slave_unit_id = $product_data->slave_unit_id;
                $order_detail->slave2_unit_id =$product_data->slave2_unit_id;
                $order_detail->price = $order_detail->total * $order_detail->unit_price;

                $order_detail->discount = $product['discount'];
                $order_detail->final_price = $product['price'] * $order_detail->total;


                $order_detail->save();



           $order->discount += $product['discount'];
           $order->price_without_promotions += $product['price'] ;
           $order->price_with_promotions += $order_detail->final_price;
           $order->final_price += $order_detail->final_price;
           $order->save();


            }


            // $order_detail->price = $order_detail->total * $order_detail->unit_price;
            // $order_detail->discount = $product['discount'];
            // $order_detail->final_price = $detail->price - $detail->discount;



        }
           // $order->final_price -= $order->amount_promotion;


        // throw new CoreExceptionOk('سفارش شما با موفقیت ثبت شد');

        return response(
            [ 'status' => true,
            'message' => 'سفارش شما با موفقیت ثبت شد',
            ]
        );


    }




    public function store(StoreRequest $request)
    {

        return   $orderIds = $this->prepareData($request, 'store');

        event(new RegisterOrder($orderIds));
        return [
            'status' => true,
            'message' => trans('messages.api.customer.order.order.store', ['count' => count($orderIds)]),
            'id' => $orderIds,
        ];
    }

    public function destroy(DestroyRequest $request)
    {
        $result = Order::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.setting.constant.destroy'),
        ];
    }

    public function destroy_invoice(DestroyInvoiceRequest $request)
    {
        $result = OrderInvoice::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.setting.constant.destroy'),
        ];
    }

    public function prepareData($request, $status, $orderData = null, $invoice = false)
    {



        $cities = array();
        if (!$this->ISCompany()) {
            throw new CoreException('امکان ثبت سفارش در پنل مستر امکان پذیر نیست');
        }
        $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');




        if ($invoice && $status == "update") {
            DetailInvoice::whereIn("order_invoice_id", $orderData)->delete();
        }

        $idOfProducts = collect($request->products)->pluck('id')->all();

        if (!count($cities)) {
            throw new CoreException('شرکت مورد نظر در شهر یا استان شرکت شما وجود ندارد');
        }
        $productsEntity = Product::whereIn('id', $idOfProducts)
            ->with([
                'PriceClasses.Customers' => function ($query) {
                    $query->where('id', auth('api')->user()->company_id);
                },
            ])
            // ->whereHas('company', function ($query) {
            //     $query->where('id', $this->ISCompany());
            // })
            ->get()->keyBy('id');


        $requestProducts = [];
        foreach ($request->products as $requestProduct) {

            $id = $requestProduct['id'];
            if (!isset($productsEntity->toArray()[$id]))
                continue;

            $company_id = $productsEntity[$id]->company_id;

            $requestProducts[$company_id][] = $requestProduct;
        }

        !empty($request->payment_method_id) ? $request->payment_method_id : 1;

        $rowDiscount = 0;
        $orderIds = [];
        //    dd($request->all());
        foreach ($requestProducts as $companyId => $products) {
            //  dd( $products);
            $paymentMethod = PaymentMethod::where([
                'payment_method_id' => $request->payment_method_id,
                'company_id' => $companyId,
            ])->first();

            $order = new Order();
            $order->status = Order::STATUS_REGISTERED;
            if(auth('api')->user()->kind == 'consumer'){
                $order->company_id = $products[0]['company_id'];
            }else{
                  $order->company_id = auth('api')->user()->company_id;
            }


            // $order->date_id = $request->date_id['timeId'];


            $changeCompany = false;

            $order->kind = Order::KIND_VENDOR;


            if (!empty($request->company_id)) {
                $changeCompany = true;
                $order->company_id = $request->company_id;
            }
            if (!empty($request->address_id['id'])) {
                $order->address_id = $request->address_id['id'];
            }


            if (
                $status == 'update'
                && !empty($request->company_id)
                && auth('api')->user()['kind'] == 'superAdmin'
            ) {
                $order->company_id = $request->company_id;
                $order->reference_date = Carbon::now();
            }

            $order->updated_by = auth('api')->id();
            $order->description = $request->description;

            if (!empty($request->visitor_id)) {
                $order->visitor_id = $request->visitor_id;
            } else {
                $order->visitor_id = null;
            }
            $order->customer_id = $request->customer_id;
            $order->payment_method_id = $request->payment_method_id;
            if ($paymentMethod) {
                $order->NewPaymentMethod()->associate($paymentMethod);
            }
            $order->amount_promotion = 0;
            $order->date_of_sending = Verta::parse($request->date_of_sending)->DateTime();
            if (!empty($request->registered_source)) {
                $order->registered_source = $request->registered_source;
            }
            $order->registered_by = auth('api')->user()->company_id;


            if ($status == 'update') {

                if ($changeCompany) {
                    OrderInvoice::find($orderData['id'])->update($order->toArray());
                    Order::find($orderData['id'])->update($order->toArray());
                } else {
                    if ($invoice == true) {
                        $orderUpdate = OrderInvoice::find($orderData['id']);
                    } else {
                        $orderUpdate = Order::find($orderData['id']);
                    }
                    $orderUpdate->update($order->toArray());
                }
            } else {

                $order->save();

            }



            //delete deteals order
            if ($status == 'update') {
                if ($invoice == true) {
                    DetailInvoice::where('order_invoice_id', $orderData['id'])->delete();
                } else {
                    Detail::where('order_id', $orderData['id'])->delete();
                }
            }





            foreach ($products as $product) {

                $productEntity = $productsEntity[$product['id']];
                if ($invoice == true) {
                    $detail = new DetailInvoice();
                } else {
                    $detail = new Detail();
                }

                if ($status == 'update') {
                    if ($changeCompany) {
                        $detailUpdateInvoice = DetailInvoice::where('order_invoice_id', $orderData['id'])
                            ->where('product_id', $product['id']);

                        $detailUpdate = Detail::where('order_id', $orderData['id'])
                            ->where('product_id', $product['id']);
                    } else {
                        if ($invoice == true) {
                            $detailUpdate = DetailInvoice::where('order_invoice_id', $orderData['id'])
                                ->where('product_id', $product['id']);
                        } else {
                            $detailUpdate = Detail::where('order_id', $orderData['id'])
                                ->where('product_id', $product['id']);
                        }
                    }
                }
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
                if (!empty($product['row_discount'])) {
                    $detail->row_discount = (int)str_replace(',', '', $product['row_discount']);
                    $rowDiscount += $detail->row_discount;
                }

                $detail->total = Product::calculateTotal(
                    $detail->master,
                    $detail->slave,
                    $detail->slave2,
                    $detail->per_master,
                    $detail->per_slave
                );

                $detail->price = $detail->total * $detail->unit_price;

                /** @var Promotions $finalPromotion */
                $detail->discount = 0;


                $detail->final_price = $detail->price - $detail->discount;

                if ($status == 'update') {

                    if (empty($detailUpdate->get()->toArray())) {
                        if ($invoice == true) {
                            $detail->order_invoice_id = $orderData['id'];
                        } else {
                            $detail->order_id = $orderData['id'];
                        }
                        $detail->save();
                    } else {

                        if ($changeCompany) {
                            $detailUpdateInvoice->update($detail->toArray());
                            $detailUpdate->update($detail->toArray());
                        } else {
                            if ($invoice == true) {
                                $detail->order_invoice_id = $orderData['id'];
                            }
                            $detailUpdate->update($detail->toArray());
                        }
                    }
                    if ((isset($detailUpdateInvoice)) ? empty($detailUpdateInvoice->get()->toArray()) : true) {
                        if ($invoice == true) {
                            $detail->order_invoice_id = $orderData['id'];
                        } else {
                            $detail->order_id = $orderData['id'];
                        }
                        $detail->save();
                    } else {
                        if ($invoice == true) {

                            $detail->order_invoice_id = $orderData['id'];
                        }
                        $detailUpdateInvoice->update($detail->toArray());
                    }
                } else {
                    $order->Details()->save($detail);
                }


                // Price & PromotionPrice
                $order->discount += $detail->discount;
                $order->price_without_promotions += $detail->price;
                $order->price_with_promotions += $detail->final_price;
                $order->final_price += $detail->final_price;
            }


            $order->final_price -= $order->amount_promotion;



            $additionPrice = 0;
            if ($request->carriage_fares) {
                $addition = Constant::where('constant_en', 'shippingPrice')->first();
                if ($invoice == true) {
                    $additionEntity = new AdditionInvoice();
                } else {
                    $additionEntity = new Addition();
                }
                $additionEntity->order_id = $order->id;

                $additionEntity->value = $request->carriage_fares;
                $additionEntity->Key()->associate($addition);
                $additionEntity->save();

                $additionPrice += $additionEntity->value;
            }


            $order->final_price += $additionPrice;

            if ($rowDiscount != 0) {
                $order->total_row_discount = $rowDiscount;
                $order->final_price_without_total_row_discount = $order->final_price;
                $order->final_price -= $rowDiscount;
            }

            if ($status == 'update') {
                if ($changeCompany) {
                    $detailUpdateInvoice = OrderInvoice::find($orderData['id'])->update($order->toArray());
                    $detailUpdate = Order::find($orderData['id'])->update($order->toArray());
                } else {
                    if ($invoice == true) {
                        $orderUpdate = OrderInvoice::find($orderData['id']);
                    } else {
                        $orderUpdate = Order::find($orderData['id']);
                    }
                    $orderUpdate->update($order->toArray());
                }
            } else {
                $order->save();
            }

            $orderIds[] = $order->id;
        }

        if ($status == 'update') {
            $productIds = [];
            foreach ($orderData->details as $detail) {
                $productIds[] = $detail->product_id;
            }

            $extraRec = array_diff($productIds, $idOfProducts);
            if (count($extraRec)) {
                foreach ($extraRec as $productId) {
                    if ($invoice == true) {
                        DetailInvoice::where('order_invoice_id', $orderData['id'])->where('product_id', $productId)->delete();
                    } else {
                        Detail::where('order_id', $orderData['id'])->where('product_id', $productId)->delete();
                    }
                }
            }
        }


        // throw new CoreExceptionOk('سفارش با موفقیت ثبت شد');
        return response(
            [ 'status' => true,
            'message' => 'سفارش شما با موفقیت ثبت شد',
            ]
        );
    }


    public function update(UpdateRequest $request, $id)
    {
        $order = Order::with([
            'details.product'
        ])->find($id);
        if (empty($order)) {
            return [
                'status' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }

        $this->prepareData($request, 'update', $order);

        return [
            'status' => true,
            'message' => trans('messages.api.customer.order.order.update'),
        ];
    }

    public function invoice_update(UpdateRequest $request, $id)
    {

        $order = OrderInvoice::with([
            'details.product'
        ]);
        if ($this->ISCompany()) {
            $order->where('company_id', $this->ISCompany());
        }
        $order = $order->find($id);
        if (empty($order)) {
            return [
                'status' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }
        $this->prepareData($request, 'update', $order, true);

        return [
            'status' => true,
            'message' => trans('messages.api.customer.order.order.update'),
        ];
    }

    public function invoice_store($orderId)
    {
        if ($this->ISCompany()) {
            $order = Order::where('company_id', $this->ISCompany())->where('id', $orderId);
            $order = $order->first();
        } else
            $order = Order::find($orderId);

        if (empty($order)) {
            return [
                'status' => true,
                'message' => 'شناسه سفارش یافت نشد'
            ];
        }

        if (!empty(OrderInvoice::find($orderId))) {
            return [
                'status' => true,
                'message' => 'سفارش مورد نظر قبلا تبدیل به پیش فاکتور شده است'
            ];
        }

        $orderInvoice = $order->replicate();
        $orderInvoiceData = $orderInvoice->toArray();
        unset($orderInvoiceData['status_translate']);
        unset($orderInvoiceData['date_of_sending_translate']);
        $orderInvoiceData['id'] = $orderId;
        $orderInvoiceData['tracking_code'] = $orderId . mt_rand(1000, 9999);
        $orderInvoiceData['created_at'] = $order->toArray()['created_at'];
        $orderInvoiceData['updated_at'] = $order->toArray()['updated_at'];
        $orderInvoiceData['deleted_at'] = $order->toArray()['deleted_at'];
        $orderInvoiceData['status'] = 'registered';
        OrderInvoice::Create($orderInvoiceData);

        $details = Detail::where('order_id', $orderId)->get();
        foreach ($details as $detail) {
            $detailInfo = Detail::find($detail['id']);
            $detailInvoice = $detailInfo->replicate();
            $detailInvoiceData = $detailInvoice->toArray();
            $detailInvoiceData['order_invoice_id'] = $detailInvoiceData['order_id'];
            unset($detailInvoiceData['order_id']);
            $detailInvoiceData['created_at'] = $detailInfo->toArray()['created_at'];
            $detailInvoiceData['updated_at'] = $detailInfo->toArray()['updated_at'];
            DetailInvoice::Create($detailInvoiceData);
        }

        $additions = Addition::where('order_id', $orderId)->get();
        foreach ($additions as $addition) {
            $additionInfo = Addition::find($addition['id']);
            $additionInvoice = $additionInfo->replicate();
            $additionInvoiceData = $additionInvoice->toArray();
            $detailInvoiceData['order_invoice_id'] = $additionInvoiceData['order_id'];
            unset($detailInvoiceData['order_id']);
            $detailInvoiceData['created_at'] = $detailInfo->toArray()['created_at'];
            $detailInvoiceData['updated_at'] = $detailInfo->toArray()['updated_at'];
            AdditionInvoice::Create($detailInvoiceData);
        }

        return [
            'status' => false,
            'message' => 'ثبت پیش فاکتور با موفقیت انجام شد'
        ];
    }

    public function states()
    {

        $data = [];
        foreach (Order::_()::STATUS as $CONSTANT_KIND) {
            $data[] = [
                'name' => $CONSTANT_KIND,
                'title' => trans("translate.order.order.$CONSTANT_KIND"),
            ];
        };

        return response()->json(['kinds' => $data]);
    }

    public function changeStatus(ChangeOrderStatusRequest $request)
    {
        $ids = collect($request->orders)->pluck('id');


        $ordersEntity = Order::whereIn('id', $ids)
            ->with(['customer.Addresses', 'visitor.User', 'Details.product.brand', 'PaymentMethod']);

        if ($this->ISCompany()) {
            $ordersEntity->where('company_id', $this->ISCompany());
        }

        $ordersEntity = $ordersEntity->get()
            ->keyBy('id');

        // dd($request->status['name']);
        $companiesOrders = [];
        $visitorsOrders = [];
        $customersOrders = [];
        foreach ($ids as $id) {



              if (!isset($ordersEntity[$id])) continue;
            $orderEntity = $ordersEntity[$id];
            $visitorsOrders[$orderEntity->visitor_id][] = $orderEntity;
            $customersOrders[$orderEntity->customer_id][] = $orderEntity;
            $companiesOrders[$orderEntity->company_id][] = $orderEntity;

            if($request->status['name'] == "confirmed"){

             //   foreach ($companiesOrders as $companyOrders) {


        // }

         }


            $orderEntity->status = $request->status['name'];
            $orderEntity->change_status_date = Carbon::now();
            $orderEntity->reject_text_id = $request->reject_text_id;
            $orderEntity->save();
        }



        $orderRejectText = Constant::where('kind', 'order_reject_text')->pluck('constant_fa', 'id')->all();
        $rejectText = '';
        foreach ($visitorsOrders as $visitorOrders) {
            if ($request->status['name'] == 'rejected') {
                if (!empty($visitorOrders[0]['reject_text_id'])) {
                    $rejectText = " به دلیل {$orderRejectText[$visitorOrders[0]['reject_text_id']]}";
                }
                $msg = "سفارش شما به شماره {$visitorOrders[0]['id']} {$rejectText} به مبلغ {$visitorOrders[0]['final_price']} رد شد";
            } else {
                $msg = "سفارش شما به شماره {$visitorOrders[0]['id']}  به مبلغ {$visitorOrders[0]['final_price']} تایید شد";
            }

            $mobile_visitor = (isset($visitorOrders[0]['visitor']['user']['mobile_number'])) ? $visitorOrders[0]['visitor']['user']['mobile_number'] : "";
            event(new ChangeStatusEvent($visitorOrders[0], $request->status['name'], $msg));

        //    event(new SendSMSEvent($msg, $mobile_visitor));

            $notification = new Notification();
            $notification->title = $msg;
            $notification->message = $msg;
            $notification->save();

            event(new NotificationStoredEvent($notification));

        }
        foreach ($customersOrders as $customerOrders) {
            if ($request->status['name'] == 'rejected') {
                if (!empty($customerOrders[0]['reject_text_id'])) {
                    $rejectText = " به دلیل {$orderRejectText[$customerOrders[0]['reject_text_id']]}";
                }
                $msg = "سفارش شما به شماره {$customerOrders[0]['id']} {$rejectText} به مبلغ {$customerOrders[0]['final_price']} رد شد";
            } else {
                $msg = "سفارش شما به شماره {$customerOrders[0]['id']}  به مبلغ {$customerOrders[0]['final_price']} تایید شد";
            }
            event(new ChangeStatusEvent($customerOrders[0], $request->status['name'], $msg));

            // event(new SendSMSEvent($msg, $customerOrders[0]['customer']['mobile_number']));

            $notification = new Notification();
            $notification->title = $msg;
            $notification->message = $msg;
            $notification->save();
            event(new NotificationStoredEvent($notification));

        }


        return [
            'status' => true,
            'message' => trans('messages.order.order.changeStatus'),
        ];
    }

    public function check(Request $request)
    {

        $userPriceClassIds = auth('api')->user()->PriceClasses->pluck('id');

        $cities = auth('api')->user()->Cities->pluck('id')->all();
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
                    $query->where('id', auth('api')->user()->company_id);
                },
            ])
            ->get()
            ->keyBy('id');

        $requestProducts = [];
        foreach ($request->products as $requestProduct) {
            $id = $requestProduct['id'];

            if (!isset($productsEntity[$id]))
                continue;

            $company_id = $productsEntity[$id]->company_id;

            $requestProducts[$company_id][] = $requestProduct;
        }

        $companyIds = array_keys($requestProducts);
        /** @var Users $companies */
        $companies = Users::whereIn('id', $companyIds)
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

        $companiesProductsData = [];
        // store order

        foreach ($requestProducts as $companyId => $products) {
            $products_id_key = collect($products)->keyBy('id');
            $finalPromotions = Promotions::check($products);

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
            $has_discount_product_in_factor = false;
            $has_fee_price_product_in_factor = false;
            foreach ($products as $product) {

                $productData = $product;

                $productEntity = $productsEntity[$product['id']];

                $productData['total'] = Product::calculateTotal(
                    $product['master'],
                    $product['slave'],
                    $product['slave2'],
                    $productEntity->per_master,
                    $productEntity->per_slave
                );

                $productData['price'] = $product['price'] ? (int)$product['price'] : $productEntity->price;
                $productData['price_total'] = $productData['total'] * $productEntity->price;

                /** @var Promotions $finalPromotion */

                $productData['discount'] = 0;

                if (

                    isset($finalPromotions[Promotions::KIND_PERCENTAGE][$product['id']])
                ) {
                    if ($finalPromotion = $finalPromotions[Promotions::KIND_PERCENTAGE][$product['id']])
                        foreach ($finalPromotion->baskets as $baskets) {
                            $productData['discount'] = ($finalPromotion->discount * $productData['price_total']) / 100;
                        }
                }

                $productData['final_price'] = $productData['price_total'] - $productData['discount'];

                $productData['markup_price'] = $productEntity->consumer_price * $productData['total'] - $productData['final_price'];
                $productData['markup_price'] = $productData['markup_price'] < 0 ? 0 : $productData['markup_price'];
                if (isset($finalPromotions[Promotions::KIND_PERCENTAGE_STRIP]) and count($finalPromotions[Promotions::KIND_PERCENTAGE_STRIP]) > 0) {
                    $final_promotion_discount = 0;
                    foreach ($finalPromotions[Promotions::KIND_PERCENTAGE_STRIP] as $finalPromotion) {
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

                    $productData['final_price'] = $productData['final_price'] - $final_promotion_discount;
                    $productData['discount'] = $productData['discount'] + $final_promotion_discount;  // discount sum with old discount
                    $productData['markup_price'] = $productEntity->consumer_price * $productData['total'] - $productData['final_price'];
                    //

                }


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


            // amount
            $amount_promotion = 0;
            foreach ($finalPromotions[Promotions::KIND_AMOUNT] as $finalPromotion) {
                $amount_promotion += $finalPromotion->amount;
            }


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

            // baskets
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

                    $productsData[$product_key]['discount'] != 0 ? round(((int)($productsData[$product_key]['price'] / $productsData[$product_key]['discount'])), 3) : 0;
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

                $final_products_fee = collect($productsData)->pluck("final_price", 'product_id')->all();
                foreach ($finalPromotions[Promotions::KIND_VOLUMETRIC] as $finalPromotion) {
                    $total_valid_product_price = 0;
                    $final_discount_promotions = 0;
                    $promotion_products_final_fee = [];
                    $valid_product_keys = [];
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

                    $discount_volumes = $finalPromotion->volumes;
                    $row_product_status = $finalPromotion->row_product_status;
                    if ($discount_volumes) {
                        $effected_promotion_by_product_id = [];
                        foreach ($discount_volumes as $discount_volume) {
                            $discount_variable_max = (int)str_replace(',', '', $discount_volume->max);
                            $discount_variable_min = (int)str_replace(',', '', $discount_volume->min);
                            $discount_variable_percent = (int)str_replace(',', '', $discount_volume->percent);;

                            if ($discount_variable_max > $total_valid_product_price and $total_valid_product_price > $discount_variable_min) {
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
                            } elseif ($discount_variable_max < $total_valid_product_price) {
                                $final_discount_promotions = ($discount_variable_percent * $total_valid_product_price) / 100;
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
            //            if ($request->coupons && isset($request->coupons[$companyId])) {
            //                /** @var Coupon $coupon */
            //                $coupon = Coupon::isValid(
            //                    $request->coupons[$companyId],
            //                    $companyId,
            //                    auth()->id()
            //                );
            //
            //                if ($coupon) {
            //                    $couponPrice = $coupon->getDiscount($companyProductsData['final_price']);
            //
            ////					$companyProductsData['discount']     += $couponPrice;
            //                    $companyProductsData['coupon_discount'] += $couponPrice;
            //                    $companyProductsData['final_price'] -= $couponPrice;
            //                    $companyProductsData['markup_price'] += $couponPrice;
            //
            //                    $factor['discount'] += $couponPrice;
            //                    $factor['final_price'] -= $couponPrice;
            //                    $factor['markup_price'] += $couponPrice;
            //                }
            //
            //            }
            //            foreach ($productsData as $key => $productData) {
            //                $productsData[$key] = $productData['discount'] +
            //            }

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

            $companiesProductsData[] = [
                'company_id' => $companyId,
                'name_fa' => !empty($companies[$companyId]->name_fa) ? $companies[$companyId]->name_fa : '',
                'name_en' => !empty($companies[$companyId]->name_en) ? $companies[$companyId]->name_en : '',
                'lat' => !empty($companies[$companyId]->lat) ? $companies[$companyId]->lat : '',
                'long' => !empty($companies[$companyId]->long) ? $companies[$companyId]->long : '',
                'items' => $productsData,
                'factor' => $companyProductsData,
            ];
        }

        $factor['companies'] = $companiesProductsData;

        return $factor;
    }

    public function deliver(DeliverRequest $request)
    {
        //        foreach ($request->order_id as $index => $orderId) {
        //            Order::where('id', $orderId)
        //                ->update([
        //                    'deliver' => 1,
        //                    'deliver_date' => Carbon::createFromTimestamp($request->deliver_date[$index])->toDateString()
        //                ]);
        //        }
        //
        //        $persianDate = Jalalian::forge(strtotime($request->deliver_date[$index]))->format('Y/m/d');
        //
        //        $ordersEntity = Order::whereIn('id', $request->order_id)
        ////            ->with('customer')
        //            ->get()
        //            ->keyBy('id');
        //
        //        foreach ($request->order_id as $id) {
        //            $orderEntity = $ordersEntity[$id];
        //        }
        //
        //        $customersOrders = [];
        //        foreach ($request->order_id as $id) {
        //            $orderEntity = $ordersEntity[$id];
        //            $customersOrders[$orderEntity->customer_id][] = $orderEntity;
        //        }
        //
        //        foreach ($customersOrders as $customerOrders) {
        //            $msg = "سفارش شما به شماره {$customerOrders[0]->id}  به مبلغ {$customerOrders[0]->final_price}  در تاریخ {$persianDate} ارسال میگردد";
        //            event(new ChangeStatusEvent($customerOrders, 'confirmed', $msg));
        //            event(new SendSMSEvent($msg, $customerOrders[0]->customer['mobile_number']));
        //        }
        //
        //
        //        return [
        //            'status' => true,
        //            'message' => 'با موفقیت ثبت شد.',
        //        ];
    }

    public function invoice_deliver(DeliverInvoiceRequest $request)
    {
        foreach ($request->order_id as $index => $orderId) {
            OrderInvoice::where('id', $orderId)
                ->update([
                    'deliver' => 1,
                    'deliver_date' => Carbon::createFromTimestamp($request->deliver_date[0])->toDateString()
                ]);
        }

        $persianDate = Jalalian::forge($request->deliver_date[0])->format('Y/m/d');

        $ordersEntity = Order::whereIn('id', $request->order_id);
        //            ->with('customer')
        if ($this->ISCompany()) {
            $ordersEntity->where('company_id', $this->ISCompany());
        }
        $ordersEntity = $ordersEntity->get()
            ->keyBy('id');

        foreach ($request->order_id as $id) {
            if (!isset($ordersEntity[$id])) continue;
            $orderEntity = $ordersEntity[$id];
        }

        $customersOrders = [];
        foreach ($request->order_id as $id) {
            $orderEntity = $ordersEntity[$id];
            $customersOrders[$orderEntity->customer_id][] = $orderEntity;
        }

        foreach ($customersOrders as $customerOrders) {
            $msg = "فاکتور نهایی به شماره {$customerOrders[0]->id}  به مبلغ {$customerOrders[0]->final_price}  در تاریخ {$persianDate} ارسال میگردد";
            event(new ChangeStatusEvent($customerOrders, 'confirmed', $msg));
            event(new SendSMSEvent($msg, $customerOrders[0]->customer['mobile_number']));
        }

        return [
            'status' => true,
            'message' => 'با موفقیت ثبت شد.',
        ];
    }

    public function payment_method_list(Request $request)
    {


      $payment = PaymentMethod::whereHas('Company'  , function($q){
            $q->where('kind' , 'superAdmin');
        })->get();

        return $payment;

        // $paymentMethods = PaymentMethod::whereHas(
        //     'PaymentMethodCustomer', function ($query) {
        //     $query->where('customer_id', '=', auth()->id());
        // })

        //         ->orWhere('payment_methods.default', '=', '1')

        //     ->with([
        //     'PaymentMethodCustomer' => function ($query) {
        //         $query->where('customer_id', '=', auth()->id());
        //     }]);

        // if ($request->comapny_ids)
        //     $paymentMethods = $paymentMethods->whereIn('company_id', $request->comapny_ids);

        // $paymentMethods = $paymentMethods->get();
        // if (!empty($paymentMethods)) {
        //     $data = [];
        //     foreach ($paymentMethods->toArray() as $paymentMethod) {
        //         $data[] = [
        //             'id' => $paymentMethod['id'],
        //             'company_id' => $paymentMethod['company_id'],
        //             'discount' => $paymentMethod['discount'],
        //             'discount_max' => $paymentMethod['discount_max'],
        //             'payment_method_id' => $paymentMethod['payment_method_id'],
        //             'payment_method' => [
		//         'id' => $paymentMethod['id'],
        //                 'constant_fa' => $paymentMethod['constant_fa'],
        //                 'constant_en' => $paymentMethod['constant_en'],
		// 	'kind' => 'payment_method',
		// 	'kind_translate' => 'روش پرداخت',
        //             ],
        //         ];
        //     }
        //     $paymentMethods = $data;
        // }
        // return $paymentMethods;
    }


    public function payment_method_show($id)
    {
        $visitorPositions = PaymentMethod::where('id', $id)->paginate();
        return $visitorPositions;
    }

    public function payment_method_store(PaymentMethodRequest $request)
    {
        $company_id = auth('api')->user()->company_id;

        if (!empty($request->company_id)) {
            $company_id = $request->company_id;
        }

        $paymentMethod = new PaymentMethod();
        $paymentMethod->constant_fa = $request->constant_fa;
        $paymentMethod->constant_en = $request->constant_en;
        $paymentMethod->company_id = $company_id;
        $paymentMethod->discount = $request->discount ?? 0;
        $paymentMethod->discount_max = $request->discount_max ?? 0;
        $paymentMethod->save();
        $updatePaymentMethod = PaymentMethod::find($paymentMethod->id);
        $updatePaymentMethod->update(['payment_method_id' => $paymentMethod->id]);

        return [
            'status' => true,
            'message' => 'روش پرداخت با موفقیت ثبت شد.',
        ];
    }


    public function payment_method_delete(Request $request)
    {
        if (!is_array($request->payment_method_ids) || !count($request->payment_method_ids)) {
            return [
                'status' => true,
                'message' => 'شناسه روش پرداخت ها باید به صورت آرایه باشد',
            ];
        }

        $paymentMethod = PaymentMethod::whereIn('id', array_unique($request->payment_method_ids));

        if ($this->ISCompany())
            $paymentMethod->where('company_id', $this->ISCompany());



        if ($paymentMethod->count()) {
            if (count(array_unique($request->payment_method_ids)) != $paymentMethod->count()) {
                return [
                    'status' => false,
                    'message' => "شناسه " . implode(" , ", array_diff(array_unique($request->payment_method_ids), $paymentMethod->pluck('id')->toArray())) . " یافت نشد"
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => "شناسه " . implode(" , ", $request->payment_method_ids) . " یافت نشد"
            ];
        }

        $found = Order::whereIn("payment_method_id", $request->payment_method_ids)->count();
        if ($found) {
            return [
                'status' => false,
                'message' => 'روش پرداخت استفاده شده در سفارش قابلیت حذف را ندارد.',
            ];
        } else {
            PaymentMethod::whereIn('id', $request->payment_method_ids)->delete();
        }




        return [
            'status' => true,
            'message' => 'روش پرداخت با موفقیت حذف شد.',
        ];
    }

    public function payment_method_update(PaymentMethodRequest $request, $id)
    {
        $paymentMethod = PaymentMethod::where('id', $id);
        if ($this->ISCompany())
            $paymentMethod->where('company_id', $this->ISCompany());

        $paymentMethod = $paymentMethod->first();
        if (empty($paymentMethod)) {
            return [
                'status' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }

        $paymentMethod->constant_fa = $request->constant_fa;
        $paymentMethod->constant_en = $request->constant_en;
        $paymentMethod->discount = $request->discount ?? 0;
        $paymentMethod->discount_max = $request->discount_max ?? 0;
        $paymentMethod->update();

        return [
            'status' => true,
            'message' => 'روش پرداخت با موفقیت بروزرسانی شد.',
        ];
    }

    public function payment_method_default(PaymentMethodDefaultRequest $request, $id)
    {
        $paymentMethod = PaymentMethod::where('id', $id);

        if ($this->ISCompany())
            $paymentMethod->where('company_id', $this->ISCompany());

        $paymentMethod = $paymentMethod->first();
        if (empty($paymentMethod)) {
            return [
                'status' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }

        $paymentMethod->default = (int) $request->status;
        $paymentMethod->update();

        return [
            'status' => true,
            'message' => 'روش پرداخت با موفقیت بروزرسانی شد.',
        ];
    }



    public function reportAll(Request $request)
    {
        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }
        $companyId = null;
        $visitors_id = null;
        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
        }

        $visitors = Visitors::_()->with(['user.CompanyRel', 'superVisitor', 'visitors']);
        $visitors = $visitors->whereHas('user', function ($query) use ($companyId) {
            if ($companyId)
                $query->where('company_id', $companyId);
        });
        $visitors_id = $visitors->get()->pluck('id')->toArray();


        if ($request->has('date_from') && $request->has('date_to')) {
            $date = [$request->date_from, $request->date_to];

            $sum = Order::whereBetween('created_at', $date);
            if ($companyId)
                $sum->where('company_id', $companyId);
            $sum = $sum->sum('final_price');

            $order_cunt = Order::whereBetween('created_at', $date);
            if ($companyId)
                $order_cunt->where('company_id', $companyId);
            $order_cunt = $order_cunt->count();

            $customer_confirm = Users::whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })->whereBetween('created_at', $date)->where('kind', 'customer')->where('approve', '1');
            if ($companyId)
                $customer_confirm = $customer_confirm->whereCities($cities);
            $customer_confirm = $customer_confirm->count();

            $all_customer = Users::whereBetween('created_at', $date)->where('kind', 'customer')->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            });
            if ($companyId)
                $all_customer = $all_customer->whereCities($cities);
            $all_customer = $all_customer->count();
            $customer_visited = Users::where('kind', 'customer')
                ->whereHas('Orders', function ($q) use ($date, $visitors_id) {
                    $q->whereBetween('created_at', $date)->whereIn('visitor_id', $visitors_id);
                })
                ->orwhereHas('ReasonForNotVisitings', function ($q) use ($date, $visitors_id) {
                    $q->whereBetween('created_at', $date)->whereIn('visitor_id', $visitors_id);
                });
            $customer_visited = $customer_visited->count();
            $customer_not_visited = $all_customer - $customer_visited;
            return [
                'orders_sum' => $sum, 'order_cunt' => $order_cunt, 'customer_confirm' => $customer_confirm, 'all_customer' => $all_customer, 'customer_not_visited' => $customer_not_visited, 'customer_visited' => $customer_visited
            ];
        } else {
            // $date = array_flip(array_flip(explode('|', $filter)));
            /*$sum = Order::where('status', 'confirmed')->sum('final_price');
            $order_cunt = Order::count();
            $customer_confirm = Users::where('kind', 'customer')->where('approve', '1')->count();
            $all_customer = Users::where('kind', 'customer')->where('status', 'active')->count();

            $customer_not_visited = Users::where('kind', 'customer')->where('status', 'active')->whereDoesntHave('Orders')->whereDoesntHave('ReasonForNotVisitings')->count();
            $customer_visited = $all_customer - $customer_not_visited;
            return [
                'orders_sum' => $sum, 'order_cunt' => $order_cunt, 'customer_confirm' => $customer_confirm, 'all_customer' => $all_customer, 'customer_not_visited' => $customer_not_visited, 'customer_visited' => $customer_visited

            ];*/
        }
    }


    public function export2(Request $request)
    {

        // return Excel::download(new OrderExportExcel($request), 'kala.xlsx');

        if ($request->ids == "false") {
            $ids = array();
        } else {
            $ids = explode(",", $request->ids);
        }


        $customerId = request('customer_id');
        $orders = Order::select('orders.*')
            ->CustomerId($customerId)
            ->with([
                'PaymentMethod',
                'customer',
                'customer.referrals' => function ($query) {
                    return $query->where('company_id', auth('api')->user()->company_id);
                },
                'customer.Provinces',
                'customer.cities',
                'customer.Areas',
                'customer.Routes',
                'customer.IntroducerCode',
                'visitor.User',
                'company',
                'invoiceOrder',
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])
            ->orderByRaw("FIELD(orders.status,'registered','confirmed','rejected')");

        if (count($ids) > 0) {
            $orders = $orders->whereIds($ids);
        }

        if (auth('api')->user()->kind == 'company') {
            $orders->where('orders.company_id', auth('api')->user()->company_id);
        }

        $orders = $orders->filter($request->all(), OrderFilter::class)->orderBy('created_at', 'desc');


        $results['data'] = array();
        $orders = $orders->get()->toArray();

        //add product to list
        $num_record = 0;
        foreach ($orders as $order) {


            $brands = array();
            foreach ($order['details'] as $details) {
                //array_push($brands, $details['product']['brand']['name_fa']);
                // foreach ($details as $product) {
                $num_record += 1;
                $text_order = "";

                if ($order['status'] == "registered")
                    $text_order = "ثبت شده";
                elseif ($order['status'] == "confirmed")
                    $text_order = "تائید شده";
                else
                    $text_order = "رد شده";





                //dd($order['customer']['areas'][0]['area']);
                $date_create = new Verta($order['created_at']);
                $date_reference_date = new Verta($order['reference_date']);
                $change_status_date = new Verta($order['change_status_date']);
                $change_deliver_date = (isset($order['deliver_date'])) ? new Verta($order['deliver_date']) : null;
                $results['data'][] = [
                    "ردیف" => $num_record,
                    "شناسه" => $order['id'],
                    "تاریخ ایجاد" => str_replace('-', '/', $date_create->formatDate()),
                    "تاریخ ارسال درخواستی" => $order['date_of_sending_translate'],
                    "تاریخ تغییر وضعیت" => str_replace('-', '/', $change_status_date->formatDate()),
                    "تاریخ ارجاع" => str_replace('-', '/', $date_reference_date->formatDate()),
                    "تاریخ ارسال قطعی" => ($change_deliver_date) ? str_replace('-', '/', $change_deliver_date->formatDate()) : "",
                    " مرجع قطعی" =>  $order['company']['name_fa'],
                    "نام ویزیتور" => (isset($order['visitor']['user']['full_name'])) ? $order['visitor']['user']['full_name'] : "",
                    "کد مشتری" => $order['customer_id'],
                    "شناسه ی مشتری در CRM" => $order['customer']['referral_id'],
                    "نام مشتری" => $order['customer']['full_name'],
                    "َشماره موبایل" => $order['customer']['mobile_number'],
                    "استان" => (isset($order['customer']['provinces'][0]['name'])) ? $order['customer']['provinces'][0]['name'] : "",
                    "شهر" => (isset($order['customer']['cities'][0]['name'])) ? $order['customer']['cities'][0]['name'] : "",
                    "نام منطقه" => (isset($order['customer']['areas'][0]['area'])) ? $order['customer']['areas'][0]['area'] : "",
                    "نام مسیر" => (isset($order['customer']['routes'][0]['route'])) ? $order['customer']['routes'][0]['route'] : "",
                    "منبع ورود سفارش" => $order['registered_source'],
                    "الویت اول" => (isset($order['order_company_priorities'][0]['company']['name_fa'])) ? $order['order_company_priorities'][0]['company']['name_fa'] : "",
                    "اولویت دوم" => (isset($order['order_company_priorities'][1]['company']['name_fa'])) ? $order['order_company_priorities'][1]['company']['name_fa'] : "",
                    "تائید کننده ی نهایی سفارش" => "پنل مدیریت",
                    "شماره حواله" => $order['transfer_number'],
                    "نام کالا" => $details['product']['name_fa'],
                    "شناسه ی کالا" => $details['product']['id'],
                    "کد سریال" => $details['product']['serial'],
                    "برند" => $details['product']['brand']['name_fa'],
                    "تعداد مبنا" => $details['master'],
                    "تعداد واحد" => $details['slave'],
                    "تعداد جزء کالا" =>  $details['slave2'],
                    " تعداد کل " =>  $details['total'],
                    "مبلغ واحد " => $details['unit_price'],
                    " مبلغ کل " =>  $details['final_price'],
                    "مبلغ تخفیف" =>  $details['row_discount'],
                    "مبلغ کل پس از تخفیف" =>  $details['final_price'] - $details['row_discount'],
                    "جمع مالیات و عوارض" => $order['customer']['introducer_code_id'],
                    "درصد مالیات" => '',
                    "جمع مبلغ کل بعلاوه جمع مالیات و عوارض" =>  '',
                    "وضعیت سفارش" => $text_order,
                    "دلیل رد سفارش" => (isset($order['reject_text']['constant_fa'])) ? $order['reject_text']['constant_fa'] : "",
                    "IMEI" => $order['imei'],
                ];
                //  }

            }
        }
        ini_set('memory_limit', '512M');


        return json_encode($results);
    }

    public function OrderAndInvoiceDifference(Request $request)
    {
        if (!$request->visitors)
            throw new CoreException('آیدی ویزیتور الزامی می باشد');

        $ids_visi = Visi::whereIn('user_id', $request->visitors)->get()->pluck('id')->toArray();
        $from_date = null;
        $to_date = null;
        if ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
        }
        if ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
        }

        $order = Detail::leftJoin('detail_invoices', 'details.order_id', '=', 'detail_invoices.order_invoice_id')
            ->leftJoin('products', 'products.id', '=', 'details.product_id')
            ->leftJoin('orders', 'orders.id', '=', 'details.order_id')
            ->where('detail_invoices.id', '<>', 'null');


        if (auth('api')->user()->kind == 'company') {
            $order->where('orders.company_id', auth('api')->user()->company_id);
        } else {
            if (!$request->company_id)
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $order->where('orders.company_id', $request->company_id);
        }
        if ($from_date)
            $order->whereDate('orders.created_at', '>=', $from_date);
        if ($to_date)
            $order->whereDate('orders.created_at', '<=', $to_date);
        $order =  $order->whereIn('orders.visitor_id', $request->visitors)
            ->select(
                'products.id as product_id',
                'products.id as id',
                'products.name_fa as name_fa',
                'orders.id as order_id',
                'details.master as order_master',
                'details.slave as order_slave',
                'details.slave2 as order_slave2',
                'details.total as order_total',
                'orders.id as invoices_id',
                'detail_invoices.master as invoices_master',
                'detail_invoices.slave as invoices_slave',
                'detail_invoices.slave2 as invoices_slave2',
                'detail_invoices.total as invoices_total',
                DB::raw('details.master - detail_invoices.master as difference_master'),
                DB::raw('details.slave2 - detail_invoices.slave2 as difference_slave2'),
                DB::raw('details.slave - detail_invoices.slave as difference_slave'),
                DB::raw('details.total - detail_invoices.total as difference_total'),
            );

        $order->orderBy('order_id', 'asc');

        if ($request->excel) {
            $order = $order->get()->toArray();
            foreach ($order as $key => $ord) {
                $order[$key]['order_master'] = $ord['order_master'] . "";
                $order[$key]['difference_master'] = $ord['difference_master'] . "";
                $order[$key]['order_slave'] = $ord['order_slave'] . "";
                $order[$key]['order_slave2'] = $ord['order_slave2'] . "";
                $order[$key]['invoices_master'] = $ord['invoices_master'] . "";
                $order[$key]['invoices_slave'] = $ord['invoices_slave'] . "";
                $order[$key]['invoices_slave2'] = $ord['invoices_slave2'] . "";
                $order[$key]['difference_master'] = abs($ord['difference_master']) . "";
                $order[$key]['difference_slave2'] = abs($ord['difference_slave2']) . "";
                $order[$key]['difference_slave'] = abs($ord['difference_slave']) . "";
                $order[$key]['difference_total'] = abs($ord['difference_total']) . "";
            }
            $excel = new Export($order, $this->DownloadExelOrderAndInvoiceDifference(), 'export sheetName');
            return Excel::download($excel, 'Export file.xlsx');
        } else
            return  $order->jsonPaginate($request->page['size']);;
    }

    private function DownloadExelOrderAndInvoiceDifference()
    {
        return  [
            "0" => 'ایدی محصول',
            "1" => 'نام محصول',
            "2" => 'شماره سفارش',
            "3" => 'سفارش کارتون',
            "4" => 'سفارش جزء',
            "5" => 'سفارش جزء2',
            "6" => 'سفارش کل',
            "7" => 'شماره فاکتور',
            "8" => 'فروش کارتون',
            "9" => 'فروش جزء',
            "10" => 'فروش جزء2',
            "11" => 'فروش کل',
            "12" => 'تفاوت کارتن',
            "13" => 'تفاوت جزء',
            "15" => 'تفاوت جزء2',
            "14" => 'تفاوت کل',

        ];
    }


    private function getDayBetweenTwoDate($start, $end)
    {
        $datetime1 = Carbon::parse($start);

        $datetime2 = Carbon::parse($end);
        $days['milady'] = array();
        $days['jalali'] = array();
        array_push($days['milady'], $datetime1->format('Y-m-d'));
        $v1 = new Verta($datetime1->format('Y-m-d'));
        array_push($days['jalali'], str_replace('-', '/', $v1->formatDate()));

        while ($datetime1 < $datetime2) {
            $datetime1 = $datetime1->addDays(1);
            $v = new Verta($datetime1->format('Y-m-d'));
            array_push($days['milady'], $datetime1->format('Y-m-d'));
            array_push($days['jalali'], str_replace('-', '/', $v->formatDate()));
        }
        return $days;
    }

    private function getCountVisitVisitor($visitor_id, $days, $filter_registered_source = null)
    {

        $data = array();
        foreach ($days as $day) {
            $count = 0;
            $order = Order::where('visitor_id', $visitor_id)->whereDate('created_at', $day);
            if (!($filter_registered_source == "NULL" || $filter_registered_source == NULL)) {
                $order->where('registered_source', $filter_registered_source);
            }
            $count = $order->get()->count();
            $reason = ReasonForNotVisiting::where('visitor_id', $visitor_id)->whereDate('created_at', $day);
            $count += $reason->get()->count();


            array_push($data, $count);
        }
        return $data;
    }


    public function getCountVisitVisitorInDays(Request $request)
    {

        if (!($request->has('date_from') && $request->has('date_to'))) {
            throw new CoreException('زمان شروع و پایان الزامیست');
        }
        if (!$request->has('filter_registered_source')) {
            throw new CoreException('منبع ورودی الزامیست');
        }

        //get visitor by company in user is login with account company
        $visitors = Visitors::_()->with(['user']);
        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $visitors = $visitors->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }
        $visitors = $visitors->get();
        //get  days ragnge in tow day
        $days = $this->getDayBetweenTwoDate($request->date_from, $request->date_to);
        $series = array();
        foreach ($visitors as $visitor) {
            $series[] = [
                "name" => $visitor->user->full_name,
                "data" => $this->getCountVisitVisitor($visitor->id, $days['milady'], $request->filter_registered_source)
            ];;
        }

        return [
            "series" => $series,
            "categories" => $days['jalali']
        ];
    }



    public function getPercentBrands(Request $request)
    {

        if (!($request->has('date_from') && $request->has('date_to'))) {
            throw new CoreException('زمان شروع و پایان الزامیست');
        }

        $date = [$request->date_from, $request->date_to];

        $companyId = null;
        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
        }
        $total = Detail::with('Product');
        if ($companyId) {
            $total->whereHas('Product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        $total->whereBetween('created_at', $date);
        $total = $total->sum('final_price');
        $results['brands'] = array();
        $results['percent'] = array();
        $brands = Brand::with(['Products' => function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        }])->get();
        foreach ($brands as $brand) {
            $product_ids = $brand->products->pluck('id');
            $count_detalis = Detail::whereIn('product_id', $product_ids)->whereBetween('created_at', $date)->sum('final_price');
            $percent =  (($total) ? $count_detalis / $total : 0) * 100;

            array_push($results['brands'], $brand->name_fa);
            array_push($results['percent'],  $percent);
        }
        return $results;
    }
    public function getPercentCategory(Request $request)
    {

        if (!($request->has('date_from') && $request->has('date_to'))) {
            throw new CoreException('زمان شروع و پایان الزامیست');
        }

        $date = [$request->date_from, $request->date_to];

        $companyId = null;
        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
        }
        $total = Detail::with('Product');
        if ($companyId) {
            $total->whereHas('Product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        $total->whereBetween('created_at', $date);
        $total = $total->sum('final_price');
        $results = array();
        $constant = Constant::with('Products')->where('kind', 'customer_category')->get();
        return $constant;
        $brands = Brand::with(['Products' => function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        }])->get();
        foreach ($brands as $brand) {
            $product_ids = $brand->products->pluck('id');
            $count_detalis = Detail::whereIn('product_id', $product_ids)->whereBetween('created_at', $date)->sum('final_price');
            $percent =  $count_detalis / $total * 100;

            $results['brands'][] = [
                "name" => $brand->name_fa,
            ];
            $results['percent'][] = [
                "percent" => $percent,
            ];
        }
        return $results;
    }


    public function behzad()
    {
        // $order_id = Order::all()->pluck('id');
        // event(new RegisterOrder($order_id));

    }

    private function ISCompany()
    {

        if (auth('api')->user()['kind'] == 'admin'  )
            return 0;
        else
            return auth('api')->user()->id;
    }
}
