<?php

namespace Core\System\Export;

use Illuminate\Http\Request;
use Core\Packages\order\Order;
use App\ModelFilters\OrderFilter;
use Core\Packages\product\Product;
use Hekmatinasser\Verta\Facades\Verta;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderExportExcel implements FromCollection
{

    /**
     * @return \Illuminate\Support\Collection
     */
    protected $request;

    public function __construct(Request $request)
    {

        $this->request = $request->all();
    }

    public function collection()
    {

        //fech data from db with releations
        $customerId = (isset($this->request['customer_id'])) ? $this->request['customer_id'] : '';
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
                'OrderCompanyPriorities.company',
                'details.product.brand',
                'RejectText'
            ])
            ->orderByRaw("FIELD(orders.status,'registered','confirmed','rejected')");
        if (auth('api')->user()->kind == 'company') {
            $orders->where('orders.company_id', auth('api')->user()->company_id);
        }

        $orders = $orders->filter($this->request, OrderFilter::class)->orderBy('created_at', 'desc');


        $results = array();
        $orders = $orders->get()->toArray();
        //set tiltle for excel
        $results[] = [
            "id" => "شناسه",
            "referral_id" => "کدمرجع",
            "company_name" => "نام شرکت",
            "customer_id" => "شماره مشتری",
            "customer.full_name" => "نام مشتری",
            "customer.mobile_number" => "موبایل",
            "customer.introducer_code.code" => "کد معروف",
            "registered_source" => "منبع ورود سفارش",
            "First_priority" => "اولویت اول سفارش",
            "First_brand" => "برند",
            "final_price" => "مبلغ",
            "payment_confirm" => "تایید پرداخت",
            "transfer_number" => "شماره حواله",
            "imei" => "شناسه دستگاه مشتری",
            "reject_text.constant_fa" => "دلیل رد سفارش",
            "status" => "وضعیت",
            "date_of_sending_translate" => "تاریخ ارسال درخواستی",
            "change_status_date" => "تاریخ تغییر وضعیت",
            "reference_date" => "تاریخ ارجاع",
            "created_at" => "تاریخ ایجاد"
        ];


        //add product to list
        foreach ($orders as $order) {
            $brands = array();

            foreach ($order['details'] as $details) {
                array_push($brands, $details['product']['brand']['name_fa']);

            }


            $results[] = [
                "id" => $order['id'],
                "referral_id" => $order['referral_id'],
                "company_name" => $order['company']['name_fa'],
                "customer_id" => $order['customer_id'],
                "customer.full_name" => $order['customer']['full_name'],
                "customer.mobile_number" => $order['customer']['mobile_number'],
                "customer.introducer_code.code" => $order['customer']['introducer_code_id'],
                "registered_source" => $order['registered_source'],
                "First_priority" => (isset($order['order_company_priorities'][0]['company']['name_fa'])) ? $order['order_company_priorities'][0]['company']['name_fa'] : '',
                "First_brand" => implode(',', $brands),
                "final_price" => $order['final_price'],
                "payment_confirm" => $order['payment_confirm'],
                "transfer_number" => $order['transfer_number'],
                "imei" => $order['imei'],
                "reject_text.constant_fa" => $order['reject_text'],
                "status" => $order['status'],
                "date_of_sending_translate" => $order['date_of_sending_translate'],
                "change_status_date" => Verta::instance($order['change_status_date']),
                "reference_date" => Verta::instance($order['reference_date']),
                "created_at" => Verta::instance($order['created_at'])
            ];
        }


        $collection = collect();
        $collection->push($results);

        return $collection;


    }
}
