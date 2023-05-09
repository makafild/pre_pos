<?php

namespace Core\System\Export;

use Illuminate\Http\Request;
use Core\Packages\order\Order;
use Core\Packages\product\Product;
use App\ModelFilters\ProductFilter;
use Core\Packages\user\Users;
use Hekmatinasser\Verta\Verta;
use Maatwebsite\Excel\Concerns\FromCollection;

class Type2ExportExcel implements FromCollection
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

        // get id company
        $company_ids = array();
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->id()];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
       $re= $this->listCustomerIsBuingInRoute($company_ids);
        $collection = collect();
        $collection->push($re);

         return $collection;

    }

    public function listCustomerIsBuingInRoute($company_ids)
    {
        $list_customer_is_buing_in_route[] = [
            "0" => 'ایدی شرکت',
            "1" => 'نام شرکت',
            "2" => 'نام استان',
            "3" => 'نام شهر',
            "4" => 'نام منطقه',
            "5" => 'نام مسیر',
            "6" => 'تعداد مشتریان خرید کرده',
            "7" => 'تعداد مشتریان خرید نکرده'
        ];
        foreach ($company_ids as $company_id) {
            $company = Users::with('Routes', 'Routes.area.city.Province')->where('id', $company_id)->first();

            foreach ($company->routes as $route) {
                $route_id = $route->id;

                $num_all_customer = Users::where('kind', 'customer')->whereHas('Routes', function ($q) use ($route_id) {
                    return $q->where('id', $route_id);
                })->pluck('id');

                $customer_is_has =  Order::with(
                    ['Company', 'Customer']
                )->where('company_id', $company_id)->whereIn('customer_id', $num_all_customer)->get()->groupBy('customer_id')->count();

                if ($num_all_customer->count()) {
                    $list_customer_is_buing_in_route[] = [
                        "0" => $company->id,
                        "1" => $company->name_fa,
                        "2" => $route->area->province->name,
                        "3" => $route->area->city->name,
                        "4" => $route->area->area,
                        "5" =>  $route->route,
                        "6" => ($customer_is_has) ? $customer_is_has : '0',
                        "7" => ($num_all_customer->count()) ? ($num_all_customer->count() - $customer_is_has) : '0'
                    ];
                }
            }
           return $list_customer_is_buing_in_route;
        }
    }
}
