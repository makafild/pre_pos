<?php

namespace Core\System\Export;

use Illuminate\Http\Request;
use Core\Packages\order\Order;
use Core\Packages\product\Product;
use App\ModelFilters\ProductFilter;
use Core\Packages\user\Users;
use Hekmatinasser\Verta\Verta;
use Maatwebsite\Excel\Concerns\FromCollection;

class Type1ExportExcel implements FromCollection
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

        ini_set('memory_limit', '512M');

        $company_ids = array();
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->id()];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }


        //set tiltle for excel
        $results[] = [
            "0" => 'ایدی شرکت',
            "1" => 'نام شرکت',
            "2" => 'از تاریخ',
            "3" => 'تا تاریخ',
            "4" => 'تعداد سفارش',
            "5" => 'تعداد کل مشتریان',
            "6" => 'تعداد مشتریان خرید کرده',
            "7" => 'تعداد مشتریان خرید نکرده'
        ];
        foreach ($company_ids as $company_id) {
            $company = Users::where('id', $company_id)->first();
            $company_city = $company->Cities;
            $num_all_customer = Users::where('kind', 'customer')->whereHas('cities', function ($q) use ($company_city) {
                return $q->whereIn('id', $company_city);
            })->pluck('id');

            $order =  Order::with(
                ['Company', 'Customer']
            )->where('company_id', $company_id)->get();
            $customer_is_has =  Order::with(
                ['Company', 'Customer']
            )->where('company_id', $company_id)->get()->groupBy('customer_id')->count();
            $start_time = new Verta($order->min('created_at'));
            $end_time = new Verta($order->max('created_at'));
            $results[] = [
                "0" =>  $company_id,
                "1" => $company->name_fa,
                "2" => str_replace('-', '/', $start_time->formatDate()),
                "3" => str_replace('-', '/', $end_time->formatDate()),
                "4" => ($order->count()) ? $order->count() : '0',
                "5" => (count($num_all_customer)) ? count($num_all_customer) : '0',
                "6" => ($customer_is_has)?$customer_is_has :'0',
                "7" => (count($num_all_customer)) ? (count($num_all_customer) - $customer_is_has) : '0'
            ];
        }
        $collection = collect();
        $collection->push($results);

        return $collection;
    }
}
