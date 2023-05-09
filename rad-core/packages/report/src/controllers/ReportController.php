<?php

namespace core\Packages\report\src\controllers;

use Carbon\Carbon;
use App\Exports\Export;
use Illuminate\Http\Request;
use App\Models\Report\Report;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use App\Models\User\VisitTime;
use Core\Packages\order\Order;
use Hekmatinasser\Verta\Verta;
use Core\Packages\gis\Province;
use Core\Packages\product\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Core\Packages\visitor\Visitors;
use Illuminate\Support\Facades\Log;
use App\ModelFilters\CustomerFilter;
use Illuminate\Pagination\Paginator;
use Maatwebsite\Excel\Facades\Excel;
use function Siler\Functional\isnull;
use Hekmatinasser\Verta\Traits\Creator;
use PHPUnit\Framework\Constraint\Count;
use Core\System\Export\Type1ExportExcel;
use Core\System\Export\Type2ExportExcel;

use Core\System\Export\Type3ExportExcel;
use App\Models\User\ReasonForNotVisiting;
use Core\Packages\not_visited\NotVisited;
use Core\System\Exceptions\CoreException;
use Illuminate\Pagination\LengthAwarePaginator;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\report\ReportsSaleProductRoute;
use Core\Packages\report\src\request\ReportRequest;

class ReportController extends CoreController
{

    public function report_1()
    {
        ini_set('memory_limit', '512M');
        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'provinces',
                'cities',
                'Areas',
                "Products",
                "Orders",
                "Routes",
                "PriceClasses",
                'addresses',
                'IntroducerCode',
                'PaymentMethodCustomer',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                }
            ]);

        if (request('from_date') && request('to_date')) {
            $customer = $customer->where('created_at', '>', request('from_date'))
                ->where('created_at', '<=', request('to_date'));
        }

        if (auth('api')->user()['kind'] == 'company') {
            $customer->where('company_id', auth('api')->user()->company_id);
        }

        if (request('provinces') && count(request('provinces'))) {
            $iProvinces = array_map('intval', request('provinces'));
            $customer = $customer->whereHas('provinces', function ($query) use ($iProvinces) {
                $query->whereIn('id', $iProvinces);
            });
        }

        $customer = $customer->get();

        $customerIds = [];
        foreach ($customer->toArray() as $cu) {
            $customerIds[] = $cu['id'];
        }

        $orders = Order::select('customer_id', 'created_at')
            ->join(DB::raw('(Select max(id) as id from orders group by customer_id) LatestMessage'), function ($join) {
                $join->on('orders.id', '=', 'LatestMessage.id');
            })->whereIN('customer_id', $customerIds);

        if (auth('api')->user()['kind'] == 'company') {
            $orders->where('company_id', auth('api')->user()->company_id);
        }

        $orders = $orders->get()->toArray();

        $customerOrders = [];
        foreach ($orders as $order) {
            $customerOrders[$order['customer_id']] = $order['created_at'];
        }

        foreach ($customer as $index => $cu) {
            if (isset($customerOrders[$cu['id']])) {
                $v = new Verta($customerOrders[$cu['id']]);
                $customer[$index]['order_last_date'] = str_replace('-', '/', $v->formatDate());
            }
        }

        $results = [];
        foreach ($customer as $index => $cu) {
            $results[$index]['id'] = $cu['id'];
            $results[$index]['full_name'] = $cu['full_name'];
            $results[$index]['mobile_number'] = $cu['mobile_number'];
            $results[$index]['categories'] = isset($cu['categories'][0]) ? $cu['categories'][0]->name : '';
            $results[$index]['provinces'] = isset($cu['provinces'][0]) ? $cu['provinces'][0]->name : '';
            $results[$index]['cities'] = isset($cu['cities'][0]) ? $cu['cities'][0]->name : '';
            $results[$index]['order_last_date'] = $cu['order_last_date'];
        }


        return $results;
    }

    public function report_2()
    {
        $provinces = '';

        if (request('provinces') && count(request('provinces'))) {
            $pr = implode(',', request('provinces'));

            $provinces = "and user_province.province_id in ({$pr}) ";
        }

        $rows = DB::select("
SELECT
Year(`created_at`) as year,
Month(`created_at`) as month,
Count(`id`) As total
FROM users
JOIN user_province ON users.id=user_province.user_id
WHERE Year(`created_at`) IS NOT NULL
{$provinces}
GROUP BY year, month
ORDER BY year, month DESC
");

        foreach ($rows as $index => $row) {
            $date = Carbon::createFromDate($row->year, $row->month, 1);
            $v = new Verta($date);
            $rows[$index]->year = $v->year;
            $rows[$index]->month = str_replace('-', '/', $v->formatWord('F'));
        }

        return $rows;
    }

    public function report_3()
    {
        $rows = DB::select("
SELECT
Year(users.created_at) as year,
Month(users.created_at) as month,
cities.name as city_name,
Count(users.id) As total
FROM users
JOIN user_province ON users.id=user_province.user_id
JOIN user_city ON users.id=user_city.user_id
JOIN cities ON user_city.city_id=cities.id
WHERE Year(users.created_at) IS NOT NULL
GROUP BY year, month,city_name
ORDER BY year, month DESC
");

        foreach ($rows as $index => $row) {
            $date = Carbon::createFromDate($row->year, $row->month, 1);
            $v = new Verta($date);
            $rows[$index]->year = $v->year;
            $rows[$index]->month = str_replace('-', '/', $v->formatWord('F'));
        }

        return $rows;
    }

    public function report_4()
    {
        ini_set('memory_limit', '512M');
        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'provinces',
                'cities',
                'Areas',
                "Products", //whereDoesntHave
                "Routes",
                "PriceClasses",
                'addresses',
                'IntroducerCode',
                'PaymentMethodCustomer',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                }
            ]);

        $customer->whereDoesntHave('Orders');

        if (request('from_date') && request('to_date')) {
            $customer = $customer->where('created_at', '>', request('from_date'))
                ->where('created_at', '<=', request('to_date'));
        }

        if (auth('api')->user()['kind'] == 'company') {
            $customer->where('company_id', auth('api')->user()->company_id);
        }

        if (request('provinces') && count(request('provinces'))) {
            $iProvinces = array_map('intval', request('provinces'));
            $customer = $customer->whereHas('provinces', function ($query) use ($iProvinces) {
                $query->whereIn('id', $iProvinces);
            });
        }

        $customer = $customer->get();

        $results = [];
        foreach ($customer as $index => $cu) {
            $results[$index]['id'] = $cu['id'];
            $results[$index]['full_name'] = $cu['full_name'];
            $results[$index]['mobile_number'] = $cu['mobile_number'];
            $results[$index]['categories'] = isset($cu['categories'][0]) ? $cu['categories'][0]->name : '';
            $results[$index]['provinces'] = isset($cu['provinces'][0]) ? $cu['provinces'][0]->name : '';
            $results[$index]['cities'] = isset($cu['cities'][0]) ? $cu['cities'][0]->name : '';
            $results[$index]['order_last_date'] = $cu['order_last_date'];
            $v = new Verta($cu['created_at']);
            $results[$index]['created_at'] = str_replace('-', '/', $v->formatDatetime());
        }


        return $results;
    }

    public function report_5()
    {
        $customers = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*');


        $customers = $customers->get();

        $results = [];
        $far = $ord = $kho = $tir = $mor = $sha = $meh = $aba = $aza = $dey = $bah = $esf = 0;
        foreach ($customers->toArray() as $index => $customer) {

            $v = new Verta($customer['created_at']);
            $month = str_replace('-', '/', $v->formatWord('F'));


            if ($month == 'فروردین') {
            }
        }


        return $results;
    }

    public function report_55()
    {
        $rows = DB::select("
SELECT
Month(users.created_at) as month,
Count(users.id) As total
FROM users
JOIN user_province ON users.id=user_province.user_id
WHERE Month(users.created_at) IS NOT NULL
GROUP BY  month
ORDER BY  month DESC
");
        $data = [];
        $totalMonth = 0;
        foreach ($rows as $index => $row) {
            $date = Carbon::createFromDate(null, $row->month, 1);
            $v = new Verta($date);
            $rows[$index]->month = str_replace('-', '/', $v->formatWord('F'));
            $data[str_replace('-', '/', $v->formatWord('F'))] = $row->total;
            $totalMonth += $row->total;
        }
        $data['total'] = $totalMonth;
        return $data;
    }

    //'Company.Provinces','Company.Areas','Company.Routes','Company.Cities'


    //this is work by process data and return real data
    /*
    public function getListOperationProductSale(Request $request)
  //  {
        ini_set('memory_limit', '5120M');
        set_time_limit(300);
        $length_row = array();
        $company_ids = array();
        $results = array();
        $num_merge = 9;
        $start_cloumen = 4;
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        $routes = Routes::with(['area', 'area.city', 'area.province'])
            ->get();

        //set tiltle for excel
        $row = [
            "00" => 'ایدی شرکت',
            "01" => 'نام شرکت',
            "02" => 'کد کالا',
            "03" => 'نام کالا',
        ];
        $sub_row = array();

        for ($i = 0; $i < $start_cloumen; $i++) {
            array_push($sub_row, '');
        }

        foreach ($routes as $route) {
            $name = $route->route;
            $row[$route->id] = $name;
            //چون مرج میکنم باید بهش فضای خالب بدم تا به دیتا های بعدیم یعنی ستون های بعدم را حذف نکند
            for ($i = 0; $i < $num_merge - 1; $i++) {
                $row[$route->id . "" . $i] = '';
            }
            array_push($sub_row, 'استان');
            array_push($sub_row, 'شهر');
            array_push($sub_row, 'منتطقه');
            array_push($sub_row, 'مجموع تعداد فاکتور');
            array_push($sub_row, 'تعداد مبنای فروش رفته');
            array_push($sub_row, 'تعداد جزئه فروش رفته');
            array_push($sub_row, 'تعداد جزئه2 فروش رفته');
            array_push($sub_row, 'تعداد کل جزئه2 فروش رفته');
            array_push($sub_row, 'مبلغ تعداد واحد فروش رفته');
        }

        $results["pp"] = $sub_row;
        foreach ($company_ids as $company_id) {
            $company = Users::with([
                'Provinces',
                'Areas',
                'Routes',
                'Areas',
                'Cities'

            ])->where('id', $company_id)->first();
            $products = Product::where('company_id', $company_id)->get();

            foreach ($products as $key => $product) {

                $results[$key] = [
                    "0" => $company_id,
                    "1" => $company->name_fa,
                    "2" => $product->id,
                    "3" => $product->name_fa,
                ];


                foreach ($routes as $route) {
                    $master = 0;
                    $total = 0;
                    $salve = 0;
                    $salve2 = 0;
                    $price = 0;
                    $orders = Order::with(['Details', 'Customer.Routes'])->whereHas('Details', function ($q) use ($product) {
                        $q->where('product_id', $product->id);
                    })
                        ->whereHas('Customer.Routes', function ($q) use ($route) {
                            $q->where('id', $route->id);
                        })
                        ->get();

                    foreach ($orders as $order) {
                        foreach ($order->details as $details) {
                            $master .= $details->master;
                            $salve .= $details->slave;
                            $salve2 .= $details->slave2;
                            $total .= $details->total;
                            $price .= $details->price;
                        }
                    }

                    $results[$key][$route->id . "provence"] = $route->area->province->name;
                    $results[$key][$route->id . "city"] = $route->area->city->name;
                    $results[$key][$route->id . "area"] = $route->area->area;
                    $results[$key][$route->id . "num_facktor"] = '' . $orders->count();
                    $results[$key][$route->id . "master"] = ($master) ? $master : '0';
                    $results[$key][$route->id . "salve"] = '' . ($salve) ? $salve : '0';
                    $results[$key][$route->id . "salve2"] = '' . ($salve2) ? $salve2 : '0';
                    $results[$key][$route->id . "total"] = '' . ($total) ? $total : '0';
                    $results[$key][$route->id . "price"] = '' . ($price) ? $price : '0';
                }
            }
        }

        $array_merge = array();

        for ($i = 1; $i <= ($routes->count() * $num_merge + $start_cloumen); $i++) {
            array_push($length_row, $this->getLenghexcel($i));
        }
        for ($i = $start_cloumen; $i <= count($length_row); $i = $i + $num_merge) {
            if ($i + ($num_merge - 1) <= Count($length_row))
                array_push($array_merge, $length_row[$i] . "1:" . $length_row[$i + ($num_merge - 1)] . "1");
        }
        array_push($array_merge, 'A1:A2');
        array_push($array_merge, 'B1:B2');
        array_push($array_merge, 'C1:C2');
        array_push($array_merge, 'D1:D2');

        $header = $row; //Export header
        $excel = new Export($results, $header, 'export sheetName');
        $excel->setMergeCells($array_merge);
        return Excel::download($excel, 'Export file.xlsx');
    }
*/
    private function getLenghexcel($num_row)
    {

        $result = array();
        $finish = false;
        do {
            if ($num_row > 26) {
                $num_repeat = floor($num_row / 26);
                $clou = $num_repeat * 26;
                $space = $num_row - $clou;
                array_push($result, $space);
                $num_row = $num_repeat;
            } else {
                array_push($result, $num_row);
                $result = array_reverse($result);
                $finish = true;
            }
        } while (!$finish);


        return  $this->english_char($result);
    }


    private function english_char($numbers)
    {

        foreach ($numbers as $key  => $number) {

            if (!$number) {

                $numbers[$key] = 26;
                $numbers[$key - 1] = $numbers[$key - 1] - 1;
            }
        }
        $result = "";
        $english = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        ];

        foreach ($numbers as $number) {

            //  $result = $result . "" . $english[$number - 1];
            $result = ($number) ? $result . "" . $english[$number - 1] : $result . "" . $english[$number];
        }

        return $result;
    }


    public function getListOperationVisitor(Request $request)
    {
        ini_set('memory_limit', '5120M');
        set_time_limit(300);
        $results = array();
        if (auth('api')->user()->kind == 'company') {
            $company_id = [auth('api')->user()->company_id];
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $company_id =  $request->company_id;
        }


        $compane_info = Users::with(['Provinces'])->where('id', $company_id)->where('kind', 'company')
            ->first();

        $visitors = Visitors::_()->with(['user.CompanyRel', 'superVisitor', 'visitors'])
            ->whereHas('user', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })->get();




        foreach ($visitors as $visitor) {

            $orders_by_visitr = Order::with(['Customer', 'Details'])
                ->whereHas('Details')
                ->where('visitor_id', $visitor->id)
                ->where('registered_source', 'حضوری')
                ->get();
            $order = Order::with(['Customer', 'Details'])
                ->whereHas('Details')
                ->where('visitor_id', $visitor->id)
                ->where('registered_source', 'حضوری')
                ->get();

            $reson_ = ReasonForNotVisiting::with(['visitor'])->where('visitor_id', $visitor->id)
                ->get();

            $results[] = [
                "0" => $company_id,
                "1" => $compane_info->name_fa,
                "2" => $visitor->user_id,
                "3" => $visitor->user->full_name,
                "4" => ($order->count() + $reson_->count()) ? $order->count() + $reson_->count() : '0',
                "5" => ($order->count()) ? $order->count() : '0',
                "6" => ($reson_->count()) ? $reson_->count() : '0',
                "7" => ($orders_by_visitr->count()) ? $orders_by_visitr->count() : '0',
                "8" => ($orders_by_visitr->sum('price_with_promotions')) ?  number_format($orders_by_visitr->sum('price_with_promotions')) : '0',
                "9" => ($orders_by_visitr->sum('final_price')) ?  number_format($orders_by_visitr->sum('final_price')) : '0',
                "10" => ($orders_by_visitr->sum('price_with_promotions')) ? number_format($orders_by_visitr->sum('price_with_promotions')) : '0',
            ];
        }


        $header = [
            "0" => 'ایدی شرکت',
            "1" => 'نام شرکت',
            "2" => 'کد ویزیتور',
            "3" => 'نام ویزیتور',
            "4" => 'تعداد ویزیت',
            "5" => 'تعداد ویزیت موفق',
            "6" => 'تعداد ویزیت ناموفق',
            "7" => 'تعداد سفارش',
            "8" => 'تعداد فروش خالص',
            "9" => 'مبلغ سفارش',
            "10" => 'مبلغ ناخالص فروش',
        ];


        $excel = new Export($results, $header, 'export sheetName');
        return Excel::download($excel, 'Export file.xlsx');
    }

    public function getListOperationCustomer()
    {
        ini_set('memory_limit', '5120M');
        set_time_limit(300);
        $results = array();
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        foreach ($company_ids as $company_id) {
            $compane_info = Users::with(['Cities'])->where('id', $company_id)->where('kind', 'company')
                ->first();
            $cities = $compane_info->cities->pluck('id');
            $customer = Users::where('kind', 'customer')->whereHas('Cities', function ($q) use ($cities) {
                $q->whereIn('id', $cities);
            })->get();
            foreach ($customer as $customer) {
                $orders = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->where('customer_id', $customer->id)
                    ->get();
                $orders_visitor = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->where('customer_id', $customer->id)
                    ->where('registered_source', 'حضوری')
                    ->get();

                $reson_ = ReasonForNotVisiting::with(['visitor'])->where('customer_id', $customer->id)
                    ->get();

                $orders_visitor_count = $orders_visitor->count();
                $reson_count = $reson_->count();
                $orders_count = $orders->count();
                $results[] = [
                    "0" => $company_id,
                    "1" => $compane_info->name_fa,
                    "2" => $customer->id,
                    "3" => $customer->full_name,
                    "4" => ($orders_visitor_count +  $reson_count) ? $orders_visitor_count +  $reson_count : '0',
                    "5" => ($orders_visitor_count) ? $orders_visitor_count : '0',
                    "6" => ($reson_count) ?  $reson_count : '0',
                    "7" => ($orders_count) ? $orders_count : '0',
                    "8" => ($orders->sum('price_with_promotions')) ? number_format($orders->sum('price_with_promotions')) : '0',
                    "9" => ($orders->sum('final_price')) ? number_format($orders->sum('final_price')) : '0',
                    "10" => ($orders->sum('price_with_promotions')) ? number_format($orders->sum('price_with_promotions')) : '0',
                ];
            }
        }

        $header = [
            "0" => 'ایدی شرکت',
            "1" => 'نام شرکت',
            "2" => 'کد مشتری',
            "3" => 'نام مشتری',
            "4" => 'تعداد دفعات ویزیت شده',
            "5" => 'تعداد ویزیت موفق',
            "6" => 'تعداد ویزیت ناموفق',
            "7" => 'تعداد سفارش',
            "8" => 'تعداد فروش خالص',
            "9" => 'مبلغ سفارش',
            "10" => 'مبلغ ناخالص فروش',
        ];


        $excel = new Export($results, $header, 'export sheetName');
        return Excel::download($excel, 'Export file.xlsx');
    }


    public function getCountCustomerIsbuy_list(Request $request)
    {
        ini_set('memory_limit', '512M');

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
        $results = array();



        if (auth('api')->user()->kind == 'company') {
            $company_id = [auth('api')->user()->company_id];
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $company_id =  $request->company_id;
        }
        $company = Users::where('id', $company_id);
        if ($request->company_name)
            $company->where('name_fa', "like", "%" . $request->company_name . "%");

        if ($to_date)
            $company->whereDate('created_at', '<=', $to_date);
        $company = $company->first();
        $company_city = $company->Cities;
        $num_all_customer = Users::where('kind', 'customer')->whereHas('cities', function ($q) use ($company_city) {
            return $q->whereIn('id', $company_city);
        });
        // if ($to_date)
        //     $num_all_customer->whereDate('created_at', '<=', "2030-03-15");
        $num_all_customer = $num_all_customer->pluck('id');
        $order =  Order::with(
            ['Company', 'Customer']
        )->where('company_id', $company_id);

        if ($from_date && $to_date) {
            $order->whereDate('created_at', '>=', $from_date);
            $order->whereDate('created_at', '<=', $to_date);
        }
        //  $order->whereBetween('created_at', [$from_date, $to_date]);
        elseif ($from_date)
            $order->whereDate('created_at', '>=', $from_date);
        elseif ($to_date)
            $order->whereDate('created_at', '<=', $to_date);
        else;
        $customer_is_has =  Order::with(
            ['Company', 'Customer']
        )->where('company_id', $company_id);
        if ($from_date && $to_date) {
            $customer_is_has->whereDate('created_at', '>=', $from_date);
            $customer_is_has->whereDate('created_at', '<=', $to_date);
        }
        // $customer_is_has->whereBetween('created_at', [$from_date, $to_date]);
        elseif ($from_date)
            $customer_is_has->whereDate('created_at', '>=', $from_date);
        elseif ($to_date)
            $customer_is_has->whereDate('created_at', '<=', $to_date);
        else;
        $customer_is_has = $customer_is_has->get()->groupBy('customer_id')->count();
        $start_time = new Verta($from_date);
        $end_time = new Verta(($to_date) ? $to_date : $order->max('created_at'));
        $order = $order->get();
        $results[] = [
            "id" =>  $company->id,
            "company_id" =>  $company->id,
            'company_name' => $company->name_fa,
            'start' => ($from_date) ? str_replace('-', '/', $start_time->formatDate()) : '-',
            'end' => str_replace('-', '/', $end_time->formatDate()),
            'number_order' => ($order->count()) ? $order->count() : '0',
            'number_all_customer' => (count($num_all_customer)) ? count($num_all_customer) : '0',
            'number_customer_is_buing' => ($customer_is_has) ? $customer_is_has : '0',
            'number_customer_is_not_buing' => (count($num_all_customer)) ? (count($num_all_customer) - $customer_is_has) : '0'
        ];


        if ($request->number_order) {
            $number_order = $request->number_order;
            $results = array_filter($results, function ($var) use ($number_order) {
                if ($var['number_order'] == $number_order) return $var;
            });
        }
        if ($request->number_all_customer) {
            $number_all_customer = $request->number_all_customer;
            $results = array_filter($results, function ($var) use ($number_all_customer) {
                if ($var['number_all_customer'] == $number_all_customer) return $var;
            });
        }
        if ($request->number_customer_is_buing) {
            $number_customer_is_buing = $request->number_customer_is_buing;
            $results = array_filter($results, function ($var) use ($number_customer_is_buing) {
                if ($var['number_customer_is_buing'] == $number_customer_is_buing) return $var;
            });
        }
        if ($request->number_customer_is_not_buing) {
            $number_customer_is_not_buing = $request->number_customer_is_not_buing;
            $results = array_filter($results, function ($var) use ($number_customer_is_not_buing) {
                if ($var['number_customer_is_not_buing'] == $number_customer_is_not_buing) return $var;
            });
        }

        $collection = collect($results);
        if (isset($request->sort['company_id'])) {
            if ($request->sort['company_id'] == 'asc')
                $collection = $collection->sortBy('company_id');
            else
                $collection = $collection->SortByDesc('company_id');
        }
        if (isset($request->sort['company_name'])) {
            if ($request->sort['company_name'] == 'asc')
                $collection = $collection->sortBy('company_name');
            else
                $collection = $collection->SortByDesc('company_name');
        }
        if (isset($request->sort['start'])) {
            if ($request->sort['start'] == 'asc')
                $collection = $collection->sortBy('start');
            else
                $collection = $collection->SortByDesc('total_customers_is_not_visited');
        }
        if (isset($request->sort['end'])) {
            if ($request->sort['end'] == 'asc')
                $collection = $collection->sortBy('end');
            else
                $collection = $collection->SortByDesc('end');
        }
        if (isset($request->sort['number_order'])) {
            if ($request->sort['number_order'] == 'asc')
                $collection = $collection->sortBy('end');
            else
                $collection = $collection->SortByDesc('end');
        }
        if (isset($request->sort['end'])) {
            if ($request->sort['end'] == 'asc')
                $collection = $collection->sortBy('end');
            else
                $collection = $collection->SortByDesc('end');
        }
        if (isset($request->sort['number_all_customer'])) {
            if ($request->sort['number_all_customer'] == 'asc')
                $collection = $collection->sortBy('end');
            else
                $collection = $collection->SortByDesc('end');
        }
        if (isset($request->sort['number_customer_is_buing'])) {
            if ($request->sort['number_customer_is_buing'] == 'asc')
                $collection = $collection->sortBy('end');
            else
                $collection = $collection->SortByDesc('number_customer_is_buing');
        }
        if (isset($request->sort['number_customer_is_not_buing'])) {
            if ($request->sort['number_customer_is_not_buing'] == 'asc')
                $collection = $collection->sortBy('end');
            else
                $collection = $collection->SortByDesc('number_customer_is_not_buing');
        }

        $results = $collection->toArray();

        if ($request->excel) {
            $header[] = [
                "0" => 'ایدی شرکت',
                "1" => 'نام شرکت',
                "2" => 'از تاریخ',
                "3" => 'تا تاریخ',
                "4" => 'تعداد سفارش',
                "5" => 'تعداد کل مشتریان',
                "6" => 'تعداد مشتریان خرید کرده',
                "7" => 'تعداد مشتریان خرید نکرده'
            ];
            $excel = new Export($results, $header, 'export sheetName');
            return Excel::download($excel, 'Export file.xlsx');
        }

        $data = $this->paginate($results, $request->page['size'], $request->page['number']);

        return json_decode(json_encode($data), true);
    }



    public function getCountCustomerIsbuyInRoute_list(Request $request)
    {
        ini_set('memory_limit', '512M');
        $from_date = null;
        $to_date = null;
        if ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d');
        }
        if ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d');
        }


        // get id company
        $company_ids = array();
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            if ($request->company_id)
                $company_ids = [$request->company_id];
            else
                $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        $list_customer_is_buing_in_route = array();
        foreach ($company_ids as $company_id) {
            $company = Users::with(
                'Routes',
                'Routes.area.city.Province'
            )
                ->where('id', $company_id);


            if ($request->routes) {

                $route = $request->routes;
                $company->whereHas('routes', function ($q) use ($route) {
                    return $q->where('route', "like", $route . "%");
                });
            }
            if ($request->area) {
                $area = $request->area;
                $company->whereHas('Routes.area', function ($q) use ($area) {
                    return $q->where('area', "like", $area . "%");
                });
            }
            if ($request->city) {
                $city = $request->city;
                $company->whereHas('Routes.area.city', function ($q) use ($city) {
                    return $q->where('name', "like", $city . "%");
                });
            }
            if ($request->province) {
                $province = $request->province;
                $company->whereHas('Routes.area.city.province', function ($q) use ($province) {
                    return $q->where('name', "like", $province . "%");
                });
            }


            //dd($company->get()[0]->Routes[0]->toArray());

            if ($request->company_name)
                $company->where('name_fa', "like", "%" . $request->company_name . "%");
            if ($to_date)
                $company->whereDate('created_at', '<=', $to_date);
            $company = $company->first();
            if (!$company) continue;
            foreach ($company->routes as $route) {
                $route_id = $route->id;

                $num_all_customer = Users::where('kind', 'customer')->whereHas('Routes', function ($q) use ($route_id) {
                    return $q->where('id', $route_id);
                });
                if ($to_date)
                    $num_all_customer->whereDate('created_at', '<=', $to_date);


                $num_all_customer = $num_all_customer->pluck('id');
                $customer_is_has =  Order::with(
                    ['Company', 'Customer']
                )->where('company_id', $company_id)->whereIn('customer_id', $num_all_customer);
                if ($from_date && $to_date) {
                    $customer_is_has->whereDate('created_at', '>=', $from_date);
                    $customer_is_has->whereDate('created_at', '<=', $to_date);
                }
                // $customer_is_has->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $customer_is_has->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $customer_is_has->whereDate('created_at', '<=', $to_date);
                else;

                // dd($from_date, $to_date);
                $customer_is_has = $customer_is_has->get()->groupBy('customer_id')->count();

                if ($num_all_customer->count()) {
                    $list_customer_is_buing_in_route[] = [
                        "company_id" => $company->id,
                        "company_name" => $company->name_fa,
                        "province_name" => $route->area->province->name,
                        "city_name" => $route->area->city->name,
                        "area_name" => $route->area->area,
                        "route_name" =>  $route->route,
                        "num_customer_buing" => ($customer_is_has) ? $customer_is_has : '0',
                        "num_customer_not_buing" => ($num_all_customer->count()) ? ($num_all_customer->count() - $customer_is_has) : '0',
                        "number_all_customer" => ($num_all_customer->count()) ? ($num_all_customer->count()) : '0'
                    ];
                }
            }
        }

        $list_customer_is_buing_in_route = $this->sortCountCustomerIsbuyInRoute($list_customer_is_buing_in_route, $request);

        if ($request->excel) {
            $header = [
                "0" => 'ایدی شرکت',
                "1" => 'نام شرکت',
                "2" => 'نام استان',
                "3" => 'نام شهر',
                "4" => 'نام منطقه',
                "5" => 'نام مسیر',
                "6" => 'تعداد مشتریان خرید کرده',
                "7" => 'تعداد مشتریان خرید نکرده',
                "8" => 'تعداد  کل مشتریان'
            ];

            $excel = new Export($list_customer_is_buing_in_route, $header, 'export sheetName');
            return Excel::download($excel, 'Export file.xlsx');
        }

        return $this->paginate($list_customer_is_buing_in_route, $request->page['size'], $request->page['number']);
    }

    public function getListOperationProvince_list(Request $request)
    {
        ini_set('memory_limit', '5120M');
        set_time_limit(300);
        $results = array();
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

        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            if ($request->company_id)
                $company_ids = [$request->company_id];
            else
                $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        foreach ($company_ids as $company_id) {
            $compane_info = Users::with(['Provinces'])->where('id', $company_id)->where('kind', 'company');
            if ($request->company_name)
                $compane_info->where('name_fa', "like", "%" . $request->company_name . "%");


            if ($request->provinces_id) {
                $provinces_id = $request->provinces_id;
                $compane_info = $compane_info->whereHas('Provinces', function ($query) use ($provinces_id) {
                    return  $query->where('province_id', $provinces_id);
                });
            }
            if ($request->province_name) {
                $province_name = $request->province_name;
                $compane_info = $compane_info->whereHas('Provinces', function ($q) use ($province_name) {
                    $q->where('name', "like", "%" . $province_name . "%");
                });
            }
            if ($to_date)
                $compane_info->whereDate('created_at', '<=', $to_date);

            $compane_info = $compane_info->first();

            if (!$compane_info) continue;
            $Provinces = $compane_info->provinces;
            foreach ($Provinces as $provinces) {

                $visitors = Visitors::_()->with(['user.CompanyRel', 'superVisitor', 'visitors', 'Routes'])
                    ->whereHas('user', function ($query) use ($company_id) {
                        $query->where('company_id', $company_id);
                    })->whereHas('routes', function ($query) use ($provinces) {
                        $query->where('province_id',  $provinces->id);
                    });
                if ($to_date)
                    $visitors->whereDate('created_at', '<=', $to_date);
                $visitors = $visitors->get();

                $Visitors_id = $visitors->pluck('user_id');
                $Visitors_id_visitor = $visitors->pluck('id');


                $row_products = array();
                $row_award = array();
                $Customer = Users::with(['Provinces'])
                    ->whereHas('Provinces', function ($q) use ($provinces) {
                        $q->where('id', $provinces->id);
                    });
                if ($to_date)
                    $Customer->whereDate('created_at', '<=', $to_date);
                $Customer = $Customer->get();

                $reson_ = ReasonForNotVisiting::with(['customer', 'visitor'])->whereIn('visitor_id', $Visitors_id)
                    ->whereHas('customer.Provinces', function ($q) use ($provinces) {
                        $q->where('id', $provinces->id);
                    });

                if ($from_date && $to_date) {
                    $reson_->whereDate('created_at', '>=', $from_date);
                    $reson_->whereDate('created_at', '<=', $to_date);
                } elseif ($from_date)
                    $reson_->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $reson_->whereDate('created_at', '<=', $to_date);
                else;
                $reson_ = $reson_->get();

                $orders_by_visitr = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->where('registered_source', 'حضوری')
                    ->whereIn('visitor_id', $Visitors_id_visitor)
                    ->whereHas('Customer.Provinces', function ($q) use ($provinces) {
                        $q->where('id', $provinces->id);
                    });
                if ($from_date && $to_date) {
                    $orders_by_visitr->whereDate('created_at', '>=', $from_date);
                    $orders_by_visitr->whereDate('created_at', '<=', $to_date);
                }
                //  $orders_by_visitr->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $orders_by_visitr->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $orders_by_visitr->whereDate('created_at', '<=', $to_date);
                else;
                $orders_by_visitr = $orders_by_visitr->get();
                $orders = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->whereIn('visitor_id', $Visitors_id_visitor)
                    ->whereHas('Customer.Provinces', function ($q) use ($provinces) {
                        $q->where('id', $provinces->id);
                    });
                if ($from_date && $to_date) {
                    $orders->whereDate('created_at', '>=', $from_date);
                    $orders->whereDate('created_at', '<=', $to_date);
                }
                //  $orders->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $orders->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $orders->whereDate('created_at', '<=', $to_date);
                else;
                $orders = $orders->get();
                $orders_confirmed = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->whereIn('visitor_id', $Visitors_id_visitor)
                    ->where('status', 'confirmed')
                    ->whereHas('Customer.Provinces', function ($q) use ($provinces) {
                        $q->where('id', $provinces->id);
                    });

                if ($from_date && $to_date) {
                    $orders_confirmed->whereDate('created_at', '>=', $from_date);
                    $orders_confirmed->whereDate('created_at', '<=', $to_date);
                }
                // $orders_confirmed->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $orders_confirmed->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $orders_confirmed->whereDate('created_at', '<=', $to_date);
                else;
                $orders_confirmed = $orders_confirmed->get();
                foreach ($orders as $order) {
                    foreach ($order->details as $details) {
                        if (!$details->prise)
                            array_push($row_products, $details->product_id);
                        else
                            array_push($row_award, $details->product_id);
                    }
                }

                $results[] = [
                    "id" => $company_id,
                    "company_name" => $compane_info->name_fa,
                    "provinces_id" => $provinces->id,
                    "provinces_code" => $provinces->name,
                    "num_visited" => ($orders_by_visitr->count() + $reson_->count()) ? ($orders_by_visitr->count() + $reson_->count()) . "" : '0',
                    "num_visited_success" => ($orders_by_visitr->count()) ? $orders_by_visitr->count() : '0',
                    "num_visitor" => ($visitors->count()) ? $visitors->count() : '0',
                    'nesbat_num_visitor_be_customer' => ($visitors->count()) ? round($Customer->count() / $visitors->count()) . "" : '0',
                    "num_row_sale" => (count($row_products)) ? count($row_products) : '0',
                    "num_row_reward" => (count($row_award)) ? count($row_award) : '0',
                    "average_row_facktor" => (count($row_products)) ? round(count($row_products) / $orders->count()) : '0',
                    "num_facktor_sale" => ($orders->count()) ? $orders->count() : '0',
                    "num_factor_by_visitor" => ($visitors->count()) ? round($orders->count() / $visitors->count()) : '0',
                    "total_price_order" => ($orders->sum('final_price')) ? number_format($orders->sum('final_price')) : '0',
                    "total_price_order_approve" => ($orders_confirmed->count()) ? $orders_confirmed->count() : '0',
                    "price_kalesh_sale" => ($orders->sum('price_with_promotions')) ? number_format($orders->sum('price_with_promotions')) : '0',
                    "return_price" => 'no',
                ];
            }
        }

        $results = $this->sortListOperationProvince($results, $request);
        if ($request->excel) {
            $header = [
                "0" => 'ایدی شرکت',
                "1" => 'نام شرکت',
                "2" => 'کد استان',
                "3" => 'نام استان',
                "4" => 'تعداد ویزیت',
                "5" => 'تعداد ویزیت موفق',
                "6" => 'تعداد ویزیتور',
                "7" => 'نسبت تعداد مشتری به ویزیتور',
                "8" => 'تعداد سطر فروش رفته',
                "9" => 'تعداد سطر جایزه',
                "10" => 'میانگین سطر فاکتور',
                "11" => 'تعداد فاکتور فروش',
                "12" => 'تعداد فاکتور به ازای هر ویزیتور',
                "13" => 'مبلغ مجموع سفارش',
                "14" => 'تعداد مجموع سفارش تایید شده',
                "15" => 'مبلغ خالص فروش',
                "16" => 'مبلغ مرجوعی',
            ];
            $excel = new Export($results, $header, 'export sheetName');
            return Excel::download($excel, 'Export file.xlsx');
        }
        return $this->paginate($results, $request->page['size'], $request->page['number']);
    }


    public function getListOperationVisitor_list(Request $request)
    {
        ini_set('memory_limit', '5120M');
        set_time_limit(300);
        $results = array();
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

        if (auth('api')->user()->kind == 'company') {
            $company_id = [auth('api')->user()->company_id];
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $company_id =  $request->company_id;
        }

        $compane_info = Users::with(['Provinces'])->where('id', $company_id)->where('kind', 'company')
            ->first();



        $visitors = Visitors::_()->with(['user.CompanyRel', 'superVisitor', 'visitors'])
            ->whereHas('user', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            });

        if ($to_date) {
            $visitors->whereDate('created_at', '<=', $to_date);
        }
        //

        $visitors = $visitors->get();

        foreach ($visitors as $visitor) {
            $count_product = 0;
            $orders_by_visitr = Order::with(['Customer', 'Details'])
                ->whereHas('Details')
                ->where('visitor_id', $visitor->id)
                ->where('registered_source', 'حضوری');
            $order = Order::with(['Customer', 'Details'])
                ->whereHas('Details')
                ->where('visitor_id', $visitor->id)
                ->where('registered_source', 'حضوری');

            if ($from_date) {
                $orders_by_visitr->whereDate('created_at', '>=', $from_date);
                $order->whereDate('created_at', '>=', $from_date);
            }
            if ($to_date) {
                $orders_by_visitr->whereDate('created_at', '<=', $to_date);
                $order->whereDate('created_at', '<=', $to_date);
            }

            $orders_by_visitr = $orders_by_visitr->get();
            $order = $order->get();

            foreach ($order as $order_details) {
                if ($order->count())
                    $count_product += $order_details->Details->count();
            }

            $reson_ = ReasonForNotVisiting::with(['visitor'])->where('visitor_id', $visitor->id);

            if ($from_date) {
                $reson_->whereDate('created_at', '>=', $from_date);
            }
            if ($to_date) {
                $reson_->whereDate('created_at', '<=', $to_date);
            }
            $reson_ = $reson_->get();

            $results[] = [
                'id' => $visitor->user_id,
                'company_id' => $company_id,
                'comapny_name' => $compane_info->name_fa,
                'visitor_id' => $visitor->user_id,
                'visitor_name' => $visitor->user->full_name,
                'num_visited' => ($order->count() + $reson_->count()) ? $order->count() + $reson_->count() : '0',
                'num_visited_success' => ($order->count()) ? $order->count() : '0',
                'num_visited_not_success' => ($reson_->count()) ? $reson_->count() : '0',
                'num_order' => ($orders_by_visitr->count()) ? $orders_by_visitr->count() : '0',
                'num_sale_kales' => ($count_product) ? $count_product : '0',
                'order_price' => ($orders_by_visitr->sum('final_price')) ? $orders_by_visitr->sum('final_price') : '0',
                'price_sale_nakhales' => ($orders_by_visitr->sum('price_with_promotions')) ? $orders_by_visitr->sum('price_with_promotions') : '0',
            ];
        }


        return $this->paginate($results, $request->page['size'], $request->page['number']);
    }



    private function paginate($items, $perPage = 1, $page = 2, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        //  $items = $items instanceof Collection ? $items : Collection::make($items);
        $row = array_slice($items, $perPage * ($page - 1), $perPage);
        return new LengthAwarePaginator($row, count($items), $perPage, $page, $options);
    }

    //this is work by process data and return real data
    //this is for value/*
    public function getListOperationProductSaletest(Request $request)
    {
        ini_set('memory_limit', '1204M');
        set_time_limit(30000);



        $company_ids = array();
        $results = array();
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        $routes = Routes::with(['area', 'area.city', 'area.province'])
            ->get();




        foreach ($company_ids as $company_id) {
            $company = Users::with([
                'Provinces',
                'Areas',
                'Routes',
                'Areas',
                'Cities'

            ])->where('id', $company_id)->first();
            $products = Product::where('company_id', $company_id)->orderBy('created_at', 'desc')->get();

            foreach ($products as $key => $product) {

                $results[$key] = [
                    "0" => $company_id,
                    "1" => $company->name_fa,
                    "2" => $product->id,
                    "3" => $product->name_fa,
                ];


                foreach ($routes as $route) {
                    $master = 0;
                    $total = 0;
                    $salve = 0;
                    $salve2 = 0;
                    $price = 0;
                    $orders = Order::with(['Details', 'Customer.Routes'])->whereHas('Details', function ($q) use ($product) {
                        $q->where('product_id', $product->id);
                    })
                        ->whereHas('Customer.Routes', function ($q) use ($route) {
                            $q->where('id', $route->id);
                        })
                        ->get();

                    foreach ($orders as $order) {
                        foreach ($order->details as $details) {
                            $master += $details->master;
                            $salve += $details->slave;
                            $salve2 += $details->slave2;
                            $total += $details->total;
                            $price += $details->price;
                        }
                    }


                    $report = new  ReportsSaleProductRoute();
                    $report->route_id = $route->id;
                    $report->product_id = $product->id;
                    $report->num_facktor = (int)$orders->count();
                    $report->num_master_sale = (int)$master;
                    $report->num_slave_sale = (int)$salve;
                    $report->num_slave2_sale = (int)$salve2;
                    $report->sum_num_slave2_sale = (int)$total;
                    $report->price_num_slave2_sale = (int)$price;
                    $report->save();
                }
            }
        }
    }


    public function getListOperationProductSale(Request $request)
    {

        if (!isset($request->areas))
            throw new CoreException('areas الزامیست');

        $area_id = $request->areas;
        ini_set('memory_limit', '5120M');
        set_time_limit(30000);
        $length_row = array();
        $company_ids = array();
        $results = array();
        $num_merge = 9;
        $start_cloumen = 4;
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        $routes = Routes::with(['area', 'area.city', 'area.province', 'User'])
            ->whereHas('area', function ($q) use ($area_id) {
                $q->where('id', $area_id);
            })
            ->whereHas('Users', function ($q) use ($company_ids) {
                $q->whereIn('id', $company_ids);
            })
            ->get();

        //set tiltle for excel
        $row = [
            "00" => 'ایدی شرکت',
            "01" => 'نام شرکت',
            "02" => 'کد کالا',
            "03" => 'نام کالا',
        ];
        $sub_row = array();

        for ($i = 0; $i < $start_cloumen; $i++) {
            array_push($sub_row, '');
        }

        foreach ($routes as $route) {
            $name = $route->route;
            $row[$route->id] = $name;
            //چون مرج میکنم باید بهش فضای خالب بدم تا به دیتا های بعدیم یعنی ستون های بعدم را حذف نکند
            for ($i = 0; $i < $num_merge - 1; $i++) {
                $row[$route->id . "" . $i] = '';
            }
            array_push($sub_row, 'استان');
            array_push($sub_row, 'شهر');
            array_push($sub_row, 'منتطقه');
            array_push($sub_row, 'مجموع تعداد فاکتور');
            array_push($sub_row, 'تعداد مبنای فروش رفته');
            array_push($sub_row, 'تعداد جزئه فروش رفته');
            array_push($sub_row, 'تعداد جزئه2 فروش رفته');
            array_push($sub_row, 'تعداد کل جزئه2 فروش رفته');
            array_push($sub_row, 'مبلغ تعداد واحد فروش رفته');
        }

        $results["pp"] = $sub_row;



        foreach ($company_ids as $company_id) {
            $company = Users::with([
                'Routes',

            ])->where('id', $company_id)->first();
            $products = Product::where('company_id', $company_id)->get();

            foreach ($products as $key => $product) {
                $product_id = $product->id;
                $reports = ReportsSaleProductRoute::with([
                    'Route',
                    'Route.area'
                ])->where('product_id', $product_id)->get()->groupBy('route_id')->toArray();
                //if($reports->count())
                // dd($reports[511]);
                $results[$key] = [
                    "0" => $company_id,
                    "1" => $company->name_fa,
                    "2" => $product->id,
                    "3" => $product->name_fa,
                ];

                foreach ($routes as $route) {
                    $exist = false;
                    $report = array();
                    if (isset($reports[$route->id])) {
                        $exist = true;
                        $report = $reports[$route->id][0];
                    }
                    $results[$key][$route->id . "provence"] = $route->area->province->name;
                    $results[$key][$route->id . "city"] = $route->area->city->name;
                    $results[$key][$route->id . "area"] = $route->area->area;
                    $results[$key][$route->id . "num_facktor"] = ($exist) ? $report['num_facktor'] : "0";
                    $results[$key][$route->id . "master"] = ($exist) ? $report['num_master_sale'] : "0";
                    $results[$key][$route->id . "salve"] = ($exist) ? $report['num_slave_sale'] : "0";
                    $results[$key][$route->id . "salve2"] = ($exist) ? $report['num_slave2_sale'] : "0";
                    $results[$key][$route->id . "total"] = ($exist) ? $report['sum_num_slave2_sale'] : "0";
                    $results[$key][$route->id . "price"] = ($exist) ? number_format($report['price_num_slave2_sale']) : "0";
                }
            }
        }

        $array_merge = array();
        for ($i = 1; $i <= ($routes->count() * $num_merge + $start_cloumen); $i++) {
            array_push($length_row, $this->getLenghexcel($i));
        }
        for ($i = $start_cloumen; $i <= count($length_row); $i = $i + $num_merge) {
            if ($i + ($num_merge - 1) <= Count($length_row))
                array_push($array_merge, $length_row[$i] . "1:" . $length_row[$i + ($num_merge - 1)] . "1");
        }
        array_push($array_merge, 'A1:A2');
        array_push($array_merge, 'B1:B2');
        array_push($array_merge, 'C1:C2');
        array_push($array_merge, 'D1:D2');
        $header = $row; //Export header
        $excel = new Export($results, $header, 'export sheetName');
        $excel->setMergeCells($array_merge);
        ini_set('memory_limit', '2024M');
        return Excel::download($excel, 'Export file.xlsx');
    }
    public function getListOperationProductSale_list2(Request $request)
    {

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
        if (!isset($request->areas))
            throw new CoreException('areas الزامیست');

        $area_id = $request->areas;
        ini_set('memory_limit', '5120M');
        set_time_limit(30000);
        $company_ids = array();
        $results = array();

        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            if ($request->company_id)
                $company_ids = [$request->company_id];
            else
                $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }

        $routes = Routes::with(['area', 'area.city', 'area.province', 'User'])
            ->whereHas('area', function ($q) use ($area_id) {
                $q->where('id', $area_id);
            })
            ->whereHas('Users', function ($q) use ($company_ids) {
                $q->whereIn('id', $company_ids);
            });

        if ($request->routes)
            $routes->where('route', 'like', '%' . $request->routes . '%');

        $routes = $routes->get();


        foreach ($company_ids as $company_id) {
            $company = Users::with([
                'Routes',
            ])->where('id', $company_id);
            if ($request->company_name_fa)
                $company->where('name_fa', "like", "%" . $request->company_name_fa . "%");
            if ($to_date)
                $company->whereDate('created_at', '<=', $to_date);
            $company = $company->first();
            if (!$company) continue;
            $products = Product::where('company_id', $company_id);
            if ($request->productCode)
                $products->where('id', $request->productCode);
            if ($request->products)
                $products->whereIn('id', $request->products);
            if ($request->brands)
                $products->whereIn('brand_id', $request->brands);
            if ($request->productName)
                $products->where('name_fa', "like", "%" . $request->productName . "%");

            if ($to_date)
                $products->whereDate('created_at', '<=', $to_date);
            $products = $products->get();
            foreach ($products as $key => $product) {
                $product_id = $product->id;
                $reports = ReportsSaleProductRoute::with([
                    'Route',
                    'Route.area'
                ])->where('product_id', $product_id);

                if ($from_date && $to_date) {
                    $reports->whereDate('created_at', '>=', $from_date);
                    $reports->whereDate('created_at', '<=', $to_date);
                }
                // $reports->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $reports->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $reports->whereDate('created_at', '<=', $to_date);
                else;
                $reports = $reports->get()->groupBy('route_id')->toArray();
                //if($reports->count())
                // dd($reports[511]);

                foreach ($routes as $route) {
                    $exist = false;
                    $report = array();
                    if (isset($reports[$route->id])) {
                        $exist = true;
                        $report = $reports[$route->id][0];
                    }
                    if ($exist) {

                        $results[] = [
                            "company_id" => $company_id,
                            "company_name" => $company->name_fa,
                            "product_id" => $product->id,
                            "product_serial" => $product->serial,
                            "product_name" => $product->name_fa,
                            "province_name" => $route->area->province->name,
                            "route" => $route->route,
                            "id" => $route->id,
                            "city" => $route->area->city->name,
                            "area" => $route->area->area,
                            "num_facktor" => ($exist) ? $report['num_facktor'] : "0",
                            "master" => ($exist) ? $report['num_master_sale'] : "0",
                            "salve" => ($exist) ? $report['num_slave_sale'] : "0",
                            "salve2" => ($exist) ? $report['num_slave2_sale'] : "0",
                            "total" => ($exist) ? $report['sum_num_slave2_sale'] : "0",
                            "price" => ($exist) ? number_format($report['price_num_slave2_sale']) : "0",
                        ];
                    }
                }
            }
            if ($request->num_facktor) {
                $num_facktor = $request->num_facktor;
                $results = array_filter($results, function ($var) use ($num_facktor) {
                    if ($var['num_facktor'] == $num_facktor) return $var;
                });
            }
            if ($request->master) {
                $master = $request->master;
                $results = array_filter($results, function ($var) use ($master) {
                    if ($var['master'] == $master) return $var;
                });
            }
            if ($request->salve) {
                $salve = $request->salve;
                $results = array_filter($results, function ($var) use ($salve) {
                    if ($var['salve'] == $salve) return $var;
                });
            }
            if ($request->salve2) {
                $salve2 = $request->salve2;
                $results = array_filter($results, function ($var) use ($salve2) {
                    if ($var['salve2'] == $salve2) return $var;
                });
            }
            if ($request->total) {
                $total = $request->total;
                $results = array_filter($results, function ($var) use ($total) {
                    if ($var['total'] == $total) return $var;
                });
            }
            if ($request->price) {
                $price = $request->price;
                $results = array_filter($results, function ($var) use ($price) {
                    if ($var['price'] == $price) return $var;
                });
            }
        }
        $data = $this->paginate($results, $request->page['size'], $request->page['number']);

        return $data;
    }


    public function getListOperationProductSale_list_old(Request $request)
    {
        $from_date = null;
        $to_date = null;
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            if ($request->company_id)
                $company_ids = [$request->company_id];
            else
                $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        if ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
        }
        if ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
        }
        if (!isset($request->areas))
            throw new CoreException('areas الزامیست');

        $area_id = $request->areas;

        $reports =  ReportsSaleProductRoute::with(['Product.Company', 'Route.area.city.Province']);
        if ($from_date && $to_date) {
            $reports->whereDate('created_at', '>=', $from_date);
            $reports->whereDate('created_at', '<=', $to_date);
        }
        //  $reports->whereBetween('created_at', [$from_date, $to_date]);
        elseif ($from_date)
            $reports->whereDate('created_at', '>=', $from_date);
        elseif ($to_date)
            $reports->whereDate('created_at', '<=', $to_date);
        else;

        $reports->whereHas('Product', function ($q) use ($company_ids) {
            return $q->where('company_id', $company_ids);
        });

        if ($request->products) {
            $products_id = $request->products;
            $reports->whereHas('Product', function ($q) use ($products_id) {
                return $q->whereIn('id', $products_id);
            });
        }
        if ($request->brands) {
            $brands_id = $request->brands;
            $reports->whereHas('Product', function ($q) use ($brands_id) {
                return $q->whereIn('brand_id', $brands_id);
            });
        }

        $reports->whereHas('Route', function ($q) use ($area_id) {
            return $q->where('area_id', $area_id);
        });

        if ($request->has('excel')) {
            $results = array();
            $reports = $reports->get();
            $header = [
                "0" => 'ایدی شرکت',
                "1" => 'نام شرکت',
                "2" => 'کد کالا',
                "3" => 'نام کالا',
                "5" => 'استان',
                "6" => 'شهر',
                "7" => 'منتطقه',
                "14" => 'مسیر',
                "8" => 'مجموع تعداد فاکتور',
                "9" => 'تعداد مبنای فروش رفته',
                "10" => 'تعداد جزئه فروش رفته',
                "11" => 'تعداد جزئه2 فروش رفته',
                "12" => 'تعداد کل جزئه2 فروش رفته',
                "13" => 'مبلغ تعداد واحد فروش رفته',
            ];

            foreach ($reports as $report) {
                $results[] = [
                    "0" => $report->product->company->id,
                    "1" => $report->product->company->name_fa,
                    "2" => $report->product->id,
                    "3" => $report->product->name_fa,
                    "5" => $report->route->area->province->name,
                    "6" => $report->route->area->city->name,
                    "7" => $report->route->area->area,
                    "14" => $report->route->route,
                    "8" => $report->num_facktor . "",
                    "9" => $report->num_master_sale . "",
                    "10" => $report->num_slave_sale . "",
                    "11" => $report->num_slave2_sale . "",
                    "12" => $report->sum_num_slave2_sale . "",
                    "13" => number_format($report->price_num_slave2_sale) . "",
                ];
            }
            $excel = new Export($results, $header, 'export sheetName');
            return Excel::download($excel, 'Export file.xlsx');
        } else {
            return $reports->jsonPaginate();
        }
    }



    public function getListOperationCustomer_list(Request $request)
    {
        $limit = ($request->page['size']) ? $request->page['size'] : 0;
        $offset = $request->page['size'] * $request->page['number'] - $request->page['size'];

        $Count_item = 0;
        ini_set('memory_limit', '512M');
        $results = array();
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
        $end = $request->page['size'] * $request->page['number'] - 1;


        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->user()->company_id];
        } else {
            if ($request->company_id)
                $company_ids = [$request->company_id];
            else
                $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }

        foreach ($company_ids as $company_id) {

            $compane_info = Users::with(['Cities'])
                ->where('id', $company_id)
                ->where('kind', 'company');
            if ($request->company_name)
                $compane_info->where('name_fa', "like", "%" . $request->company_name . "%");
            if ($to_date)
                $compane_info->whereDate('created_at', '<=', $to_date);
            $compane_info = $compane_info->first();
            if (!$compane_info) continue;

            $cities = $compane_info->cities->pluck('id');
            $customer = Users::where('kind', 'customer')
                ->whereHas('Cities', function ($q) use ($cities) {
                    $q->whereIn('id', $cities);
                });
            if ($request->customer_id)
                $customer->where('id', $request->customer_id);
            if ($request->customer_name)
                $customer->where('first_name', 'like', '%' . $request->customer_name . '%')->orwhere('last_name', 'like', '%' . $request->customer_name . '%');
            if ($to_date)
                $customer->whereDate('created_at', '<=', $to_date);
            $Count_item = $customer->get()->count();
            $customer = $customer->offset($offset)->take($limit)->get();
            foreach ($customer as $key => $customer) {
                $orders = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->where('customer_id', $customer->id);
                if ($from_date && $to_date) {
                    $orders->whereDate('created_at', '>=', $from_date);
                    $orders->whereDate('created_at', '<=', $to_date);
                }
                //  $orders->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $orders->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $orders->whereDate('created_at', '<=', $to_date);
                else;
                $orders = $orders->get();

                $orders_visitor = Order::with(['Customer', 'Details'])
                    ->whereHas('Details')
                    ->where('customer_id', $customer->id)
                    ->where('registered_source', 'حضوری');
                if ($from_date && $to_date) {
                    $orders_visitor->whereDate('created_at', '>=', $from_date);
                    $orders_visitor->whereDate('created_at', '<=', $to_date);
                }
                //  $orders_visitor->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $orders_visitor->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $orders_visitor->whereDate('created_at', '<=', $to_date);
                else;
                $orders_visitor = $orders_visitor->get();

                $reson_ = ReasonForNotVisiting::with(['visitor'])->where('customer_id', $customer->id);
                if ($from_date && $to_date) {
                    $reson_->whereDate('created_at', '>=', $from_date);
                    $reson_->whereDate('created_at', '<=', $to_date);
                }
                //$reson_->whereBetween('created_at', [$from_date, $to_date]);
                elseif ($from_date)
                    $reson_->whereDate('created_at', '>=', $from_date);
                elseif ($to_date)
                    $reson_->whereDate('created_at', '<=', $to_date);
                else;
                $reson_ = $reson_->get();
                $results[] = [
                    'company_id' => $company_id,
                    'company_info' => $compane_info->name_fa,
                    'user_id' => $customer->id,
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'num_visited' => ($orders_visitor->count() + $reson_->count()) ? $orders_visitor->count() + $reson_->count() : '0',
                    'num_visited_success' => ($orders_visitor->count()) ? $orders_visitor->count() : '0',
                    'num_visited_not_success' => ($reson_->count()) ? $reson_->count() : '0',
                    'num_order' => ($orders->count()) ? $orders->count() : '0',
                    'num_sale_khales' => ($orders->sum('price_with_promotions')) ? number_format($orders->sum('price_with_promotions')) : '0',
                    'order_price' => ($orders->sum('final_price')) ? number_format($orders->sum('final_price') . "") : '0',
                    'price_sale_nakhales' => ($orders->sum('price_with_promotions')) ? number_format($orders->sum('price_with_promotions')) : '0',
                ];
            }
        }


        if ($request->num_visited) {
            $num_visited = $request->num_visited;
            $results = array_filter($results, function ($var) use ($num_visited) {
                if ($var['num_visited'] == $num_visited) return $var;
            });
        }
        if ($request->num_visited_success) {
            $num_visited_success = $request->num_visited_success;
            $results = array_filter($results, function ($var) use ($num_visited_success) {
                if ($var['num_visited_success'] == $num_visited_success) return $var;
            });
        }
        if ($request->num_visited_not_success) {
            $num_visited_not_success = $request->num_visited_not_success;
            $results = array_filter($results, function ($var) use ($num_visited_not_success) {
                if ($var['num_visited_not_success'] == $num_visited_not_success) return $var;
            });
        }
        if ($request->num_order) {
            $num_order = $request->num_order;
            $results = array_filter($results, function ($var) use ($num_order) {
                if ($var['num_order'] == $num_order) return $var;
            });
        }
        if ($request->num_sale_khales) {
            $num_sale_khales = $request->num_sale_khales;
            $results = array_filter($results, function ($var) use ($num_sale_khales) {
                if ($var['num_sale_khales'] == $num_sale_khales) return $var;
            });
        }
        if ($request->order_price) {
            $order_price = $request->order_price;
            $results = array_filter($results, function ($var) use ($order_price) {
                if ($var['order_price'] == $order_price) return $var;
            });
        }
        if ($request->price_sale_nakhales) {
            $price_sale_nakhales = $request->price_sale_nakhales;
            $results = array_filter($results, function ($var) use ($price_sale_nakhales) {
                if ($var['price_sale_nakhales'] == $price_sale_nakhales) return $var;
            });
        }


        return $this->paginateCustom($results, $request->page['size'], $request->page['number'], $Count_item);
    }


    private function paginateCustom($items, $perPage = 1, $page = 2, $Count_item = 1, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        //  $items = $items instanceof Collection ? $items : Collection::make($items);
        $row = array_slice($items, $perPage * ($page - 1), $perPage);
        return new LengthAwarePaginator($items, $Count_item, $perPage, $page, $options);
    }


    private function sortListOperationProvince($results, $request)
    {

        if ($request->num_visited) {
            $num_visited = $request->num_visited;
            $results = array_filter($results, function ($var) use ($num_visited) {
                if ($var['num_visited'] == $num_visited) return $var;
            });
        }
        if ($request->num_visited_success) {
            $num_visited_success = $request->num_visited_success;
            $results = array_filter($results, function ($var) use ($num_visited_success) {
                if ($var['num_visited_success'] == $num_visited_success) return $var;
            });
        }
        if ($request->num_visitor) {
            $num_visitor = $request->num_visitor;
            $results = array_filter($results, function ($var) use ($num_visitor) {
                if ($var['num_visitor'] == $num_visitor) return $var;
            });
        }
        if ($request->nesbat_num_visitor_be_customer) {
            $nesbat_num_visitor_be_customer = $request->nesbat_num_visitor_be_customer;
            $results = array_filter($results, function ($var) use ($nesbat_num_visitor_be_customer) {
                if ($var['nesbat_num_visitor_be_customer'] == $nesbat_num_visitor_be_customer) return $var;
            });
        }
        if ($request->num_row_sale) {
            $num_row_sale = $request->num_row_sale;
            $results = array_filter($results, function ($var) use ($num_row_sale) {
                if ($var['num_row_sale'] == $num_row_sale) return $var;
            });
        }
        if ($request->num_row_reward) {
            $num_row_reward = $request->num_row_reward;
            $results = array_filter($results, function ($var) use ($num_row_reward) {
                if ($var['num_row_reward'] == $num_row_reward) return $var;
            });
        }
        if ($request->average_row_facktor) {
            $average_row_facktor = $request->average_row_facktor;
            $results = array_filter($results, function ($var) use ($average_row_facktor) {
                if ($var['average_row_facktor'] == $average_row_facktor) return $var;
            });
        }
        if ($request->num_facktor_sale) {
            $num_facktor_sale = $request->num_facktor_sale;
            $results = array_filter($results, function ($var) use ($num_facktor_sale) {
                if ($var['num_facktor_sale'] == $num_facktor_sale) return $var;
            });
        }
        if ($request->num_factor_by_visitor) {
            $num_factor_by_visitor = $request->num_factor_by_visitor;
            $results = array_filter($results, function ($var) use ($num_factor_by_visitor) {
                if ($var['num_factor_by_visitor'] == $num_factor_by_visitor) return $var;
            });
        }
        if ($request->total_price_order) {
            $total_price_order = $request->total_price_order;
            $results = array_filter($results, function ($var) use ($total_price_order) {
                if ($var['total_price_order'] == $total_price_order) return $var;
            });
        }
        if ($request->total_price_order_approve) {
            $total_price_order_approve = $request->total_price_order_approve;
            $results = array_filter($results, function ($var) use ($total_price_order_approve) {
                if ($var['total_price_order_approve'] == $total_price_order_approve) return $var;
            });
        }
        if ($request->return_price) {
            $return_price = $request->return_price;
            $results = array_filter($results, function ($var) use ($return_price) {
                if ($var['return_price'] == $return_price) return $var;
            });
        }
        if ($request->price_kalesh_sale) {
            $price_kalesh_sale = $request->price_kalesh_sale;
            $results = array_filter($results, function ($var) use ($price_kalesh_sale) {
                if ($var['price_kalesh_sale'] == $price_kalesh_sale) return $var;
            });
        }


        $collection = collect($results);

        if (isset($request->sort['company_name'])) {
            if ($request->sort['company_name'] == 'asc')
                $collection = $collection->sortBy('company_name');
            else
                $collection = $collection->SortByDesc('company_name');
        }
        if (isset($request->sort['provinces_id'])) {
            if ($request->sort['provinces_id'] == 'asc')
                $collection = $collection->sortBy('provinces_id');
            else
                $collection = $collection->SortByDesc('provinces_id');
        }
        if (isset($request->sort['provinces_code'])) {
            if ($request->sort['provinces_code'] == 'asc')
                $collection = $collection->sortBy('provinces_code');
            else
                $collection = $collection->SortByDesc('provinces_code');
        }
        if (isset($request->sort['num_visited'])) {
            if ($request->sort['num_visited'] == 'asc')
                $collection = $collection->sortBy('num_visited');
            else
                $collection = $collection->SortByDesc('num_visited');
        }
        if (isset($request->sort['num_visited_success'])) {
            if ($request->sort['num_visited_success'] == 'asc')
                $collection = $collection->sortBy('num_visited_success');
            else
                $collection = $collection->SortByDesc('num_visited_success');
        }
        if (isset($request->sort['num_visitor'])) {
            if ($request->sort['num_visitor'] == 'asc')
                $collection = $collection->sortBy('num_visitor');
            else
                $collection = $collection->SortByDesc('num_visitor');
        }
        if (isset($request->sort['nesbat_num_visitor_be_customer'])) {
            if ($request->sort['nesbat_num_visitor_be_customer'] == 'asc')
                $collection = $collection->sortBy('nesbat_num_visitor_be_customer');
            else
                $collection = $collection->SortByDesc('nesbat_num_visitor_be_customer');
        }
        if (isset($request->sort['num_row_sale'])) {
            if ($request->sort['num_row_sale'] == 'asc')
                $collection = $collection->sortBy('num_row_sale');
            else
                $collection = $collection->SortByDesc('num_row_sale');
        }
        if (isset($request->sort['num_row_reward'])) {
            if ($request->sort['num_row_reward'] == 'asc')
                $collection = $collection->sortBy('num_row_reward');
            else
                $collection = $collection->SortByDesc('num_row_reward');
        }
        if (isset($request->sort['average_row_facktor'])) {
            if ($request->sort['average_row_facktor'] == 'asc')
                $collection = $collection->sortBy('average_row_facktor');
            else
                $collection = $collection->SortByDesc('average_row_facktor');
        }
        if (isset($request->sort['num_facktor_sale'])) {
            if ($request->sort['num_facktor_sale'] == 'asc')
                $collection = $collection->sortBy('num_facktor_sale');
            else
                $collection = $collection->SortByDesc('num_facktor_sale');
        }
        if (isset($request->sort['num_factor_by_visitor'])) {
            if ($request->sort['num_factor_by_visitor'] == 'asc')
                $collection = $collection->sortBy('num_factor_by_visitor');
            else
                $collection = $collection->SortByDesc('num_factor_by_visitor');
        }
        if (isset($request->sort['total_price_order'])) {
            if ($request->sort['total_price_order'] == 'asc')
                $collection = $collection->sortBy('total_price_order');
            else
                $collection = $collection->SortByDesc('total_price_order');
        }
        if (isset($request->sort['total_price_order_approve'])) {
            if ($request->sort['total_price_order_approve'] == 'asc')
                $collection = $collection->sortBy('total_price_order_approve');
            else
                $collection = $collection->SortByDesc('total_price_order_approve');
        }
        if (isset($request->sort['price_kalesh_sale'])) {
            if ($request->sort['price_kalesh_sale'] == 'asc')
                $collection = $collection->sortBy('price_kalesh_sale');
            else
                $collection = $collection->SortByDesc('price_kalesh_sale');
        }
        if (isset($request->sort['return_price'])) {
            if ($request->sort['return_price'] == 'asc')
                $collection = $collection->sortBy('return_price');
            else
                $collection = $collection->SortByDesc('return_price');
        }


        return $collection->all();
    }




    private function sortCountCustomerIsbuyInRoute($results, $request)
    {
        if ($request->number_all_customer) {
            $num_customer_buing = $request->number_all_customer;
            $results = array_filter($results, function ($var) use ($num_customer_buing) {
                if ($var['number_all_customer'] == $num_customer_buing) return $var;
            });
        }
        if ($request->number_customer_is_buing) {
            $number_all_customer = $request->number_customer_is_buing;
            $results = array_filter($results, function ($var) use ($number_all_customer) {
                if ($var['num_customer_buing'] == $number_all_customer) return $var;
            });
        }
        if ($request->number_customer_is_not_buing) {
            $num_customer_not_buing = $request->number_customer_is_not_buing;
            $results = array_filter($results, function ($var) use ($num_customer_not_buing) {
                if ($var['num_customer_not_buing'] == $num_customer_not_buing) return $var;
            });
        }
        $collection = collect($results);

        if (isset($request->sort['company_id'])) {
            if ($request->sort['company_id'] == 'asc')
                $collection = $collection->sortBy('company_id');
            else
                $collection = $collection->SortByDesc('company_id');
        }
        if (isset($request->sort['company_name'])) {
            if ($request->sort['company_name'] == 'asc')
                $collection = $collection->sortBy('company_name');
            else
                $collection = $collection->SortByDesc('company_name');
        }
        if (isset($request->sort['province'])) {
            if ($request->sort['province'] == 'asc')
                $collection = $collection->sortBy('province_name');
            else
                $collection = $collection->SortByDesc('province_name');
        }
        if (isset($request->sort['city'])) {
            if ($request->sort['city'] == 'asc')
                $collection = $collection->sortBy('city_name');
            else
                $collection = $collection->SortByDesc('city_name');
        }
        if (isset($request->sort['area'])) {
            if ($request->sort['area'] == 'asc')
                $collection = $collection->sortBy('area_name');
            else
                $collection = $collection->SortByDesc('area_name');
        }
        if (isset($request->sort['routes'])) {
            if ($request->sort['routes'] == 'asc')
                $collection = $collection->sortBy('route_name');
            else
                $collection = $collection->SortByDesc('route_name');
        }
        if (isset($request->sort['number_customer_is_buing'])) {
            if ($request->sort['number_customer_is_buing'] == 'asc')
                $collection = $collection->sortBy('num_customer_buing');
            else
                $collection = $collection->SortByDesc('num_customer_buing');
        }
        if (isset($request->sort['number_all_customer'])) {
            if ($request->sort['number_all_customer'] == 'asc')
                $collection = $collection->sortBy('number_all_customer');
            else
                $collection = $collection->SortByDesc('number_all_customer');
        }
        if (isset($request->sort['number_customer_is_not_buing'])) {
            if ($request->sort['number_customer_is_not_buing'] == 'asc')
                $collection = $collection->sortBy('num_customer_not_buing');
            else
                $collection = $collection->SortByDesc('num_customer_not_buing');
        }

        return $collection->all();
    }

    public function getListOperationProductSale_list(Request $request)
    {
        ini_set('memory_limit', '5120M');
        if ($request->has('provinces'))
            if (!is_array($request->provinces)) {
                throw new CoreException('استان، باید ارایه باشد');
            }
        if ($request->has('cities'))
            if (!is_array($request->cities)) {
                throw new CoreException('شهر، باید ارایه باشد');
            }

        if ($request->has('areas'))
            if (!is_array($request->areas)) {
                throw new CoreException('مناطق، باید ارایه باشد');
            }


        if (auth('api')->user()->kind == 'company') {
            $company_id = auth('api')->user()->company_id;
        } else {
            $company_id = $request->company_id;
        }

        if (!$company_id)
            throw new CoreException('شناسه ی کمپانی الزامیست');

        $from_date = null;
        $to_date = null;

        if ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d');
        }
        if ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d');
        }


        //   $filter_date = 'AND details.created_at >= ' . $from_date . ' AND details.created_at <= ' . $to_date;
        $filter_date = 'AND details.created_at BETWEEN ' . $from_date . ' AND ' . $to_date;
        $query = Routes::select([
            'products.serial as product_serial',
            'products.id as product_id',
            'products.name_fa  as product_name',
            DB::raw('(SELECT users.id FROM users WHERE users.id=' . $company_id . ') AS company_id'),
            DB::raw('(SELECT users.name_fa  FROM users WHERE users.id=' . $company_id . ') AS company_name'),
            'provinces.name AS provinces_name',
            'cities.name AS city_name',
            'areas.area AS name_area',
            'routes.route AS route_name',
            DB::raw('(SELECT COUNT(details.order_id) AS total  FROM details WHERE details.product_id=products.id ' . $filter_date . ') AS num_factor'),
            DB::raw('(SELECT SUM(details.master) AS master_total FROM details WHERE details.product_id=products.id ' . $filter_date . ') AS sum_master'),
            DB::raw('(SELECT SUM(details.slave) AS slave_total FROM details WHERE details.product_id=products.id ' . $filter_date . ') AS sum_slave'),
            DB::raw('(SELECT SUM(details.slave2) AS slave2_total FROM details WHERE details.product_id=products.id ' . $filter_date . ') AS sum_slave2'),
            DB::raw('(SELECT SUM(details.total) AS sumtotal FROM details WHERE details.product_id=products.id ' . $filter_date . ') AS sum_total'),
            DB::raw('(SELECT SUM(details.final_price) AS final_price FROM details WHERE details.product_id=products.id ' . $filter_date . ') AS sum_final_price')
        ])
            ->join('areas', 'areas.id', '=', 'routes.area_id')
            ->join('cities', 'cities.id', '=', 'areas.city_id')
            ->join('provinces', 'provinces.id', '=', 'cities.province_id')
            ->join('products', 'products.company_id', '=',  'company_id');


        if ($request->provinces) {
            $query->whereIn('provinces.id', $request->provinces);
        }
        if ($request->cities) {
            $query->whereIn('cities.id', $request->cities);
        }
        if ($request->areas) {
            $query->whereIn('areas.id', $request->areas);
        }

        if ($request->company_id) {
            $query->where('company_id', $company_id);
        }
        if ($request->products) {
            $query->whereIn('products.id', $request->products);
        }


        $results = $query->get()->whereNotNull('sum_total');
        //   return $results;

        return $this->paginate((count($results) > 0) ? $results : array(), $request->page['size'], $request->page['number']);

        // return $query->jsonPaginate();
    }
}
