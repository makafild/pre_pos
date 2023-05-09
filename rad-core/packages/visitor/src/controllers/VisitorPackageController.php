<?php

namespace core\Packages\visitor\src\controllers;


use DateTime;
use Throwable;
use Carbon\Carbon;
use App\Exports\Export;
use App\Models\Order\Order;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Core\Packages\gis\Routes;
use Core\Packages\order\Visi;
use Core\Packages\user\Users;
use App\Models\User\VisitTime;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\ModelFilters\VisitorFilter;
use Core\Packages\visitor\Visitors;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Maatwebsite\Excel\Facades\Excel;
use function Siler\Functional\isnull;
use Hekmatinasser\Verta\Facades\Verta;
use Hekmatinasser\Verta\Verta as verta1;
use App\Models\User\ReasonForNotVisiting;
use Core\Packages\not_visited\NotVisited;
use Core\Packages\order\Order as OrderOrder;
use Core\System\Exceptions\CoreException;
use Core\Packages\tour_visit\TourVisitors;
use Core\Packages\visitor\UnvisitedReport;

use Illuminate\Pagination\LengthAwarePaginator;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\visitor\src\request\StoreRequest;
use Core\Packages\visitor\src\request\UpdateRequest;
use Core\Packages\visitor\src\request\DestroyRequest;
use Core\Packages\visitor\src\request\VisitorRouteRequest;
use Core\Packages\visitor\src\request\UnvisitedReportRequest;
use Core\Packages\visitor\src\request\ReasonForNotVisitingDeleteDestroyRequest;

/**
 * Class VisitorPackageController
 *
 * @package Core\Packages\Visitor\src\controllers
 */
class VisitorPackageController extends CoreController
{

    private $_fillable = [
        'user_id',
        'is_super_visitor',
        'password',
        'email',
        'mobile_number',
        'first_name',
        'last_name',
        'ref_id',
        'visitors',
        'group_id',

    ];
    protected $_assign_route_fillable = [
        "visitor_id",
        "route_id",
        "start_at",
        "end_at",
    ];

    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        $visitors = Visitors::_()->with(['user.CompanyRel','user.Group', 'superVisitor', 'visitors']);

        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $visitors = $visitors->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }
        $visitors = $visitors->where("is_super_visitor", 0);

        $visitors = $visitors->filter($request->all(), VisitorFilter::class)->orderBy($sort, $order)->jsonPaginate($limit);
        return $visitors;
    }
    public function super(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        $visitors = Visitors::_()->with(['user.CompanyRel','user.Group', 'superVisitor', 'visitors']);

        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $visitors = $visitors->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $visitors = $visitors->where("is_super_visitor", 1);

        $visitors = $visitors->filter($request->all(), VisitorFilter::class)->orderBy($sort, $order)->jsonPaginate($limit);
        return $visitors;
    }
    public function super_show($id)
    {



        $visitors = Visitors::_()->with(['user.CompanyRel','user.Group', 'superVisitor', 'visitors']);

        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $visitors = $visitors->whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $visitors = $visitors->where("is_super_visitor", 1)->where('id',$id);

    }

    public function list(Request $request)
    {
        $type = $request->type;
        $result = Visitors::_()->list($request, null, $type);
        return $this->responseHandler($result);
    }

    public function routes($id)
    {
        $result = Visitors::_()->where('user_id', $id)->with(['Routes', 'superVisitor', 'visitors'])->jsonPaginate();
        return $result;
    }

    public function show($id, Request $request)
    {

        $result = Visitors::_()->list($request, $id);
        return $this->responseHandler($result);
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->only($this->_fillable);
        $payload['is_super_visitor'] = 0;
        $result = Visitors::_()->store($payload);
        $message = $payload['is_super_visitor'] == true ?
            trans('messages.visitor.visitor.store.super_visitor') :
            trans('messages.visitor.visitor.store.visitor');

        return [
            'status' => true,
            'company_id' => auth('api')->user()->company_id,
            'full_name' => $payload['first_name'] . " " . $payload['last_name'],
            'id' => $result,
            'message' => $message
        ];
    }
    public function storeSuper(StoreRequest $request)
    {
        $payload = $request->only($this->_fillable);
        $payload['is_super_visitor'] = 1;
        $result = Visitors::_()->store($payload);
        $message = $payload['is_super_visitor'] == true ?
            trans('messages.visitor.visitor.store.super_visitor') :
            trans('messages.visitor.visitor.store.visitor');

        return [
            'status' => true,
            'company_id' => auth('api')->user()->company_id,
            'full_name' => $payload['first_name'] . " " . $payload['last_name'],
            'id' => $result,
            'message' => $message
        ];
    }

    public function update($id, UpdateRequest $request)
    {

        $payload = $request->only($this->_fillable);
          $issuper=Visi::find($id);
          if(!$issuper->is_super_visitor)
        $result = Visitors::_()->updateR($id, $payload);
        $message =
            trans('messages.visitor.visitor.update.visitor');

        return [
            'status' => true,
            'message' => $message
        ];
    }
    public function updateSuper($id, UpdateRequest $request)
    {

        $payload = $request->only($this->_fillable);
        $issuper=Visi::find($id);
        if($issuper->is_super_visitor)
        $result = Visitors::_()->updateR($id, $payload);
        $message =
            trans('messages.visitor.visitor.update.super_visitor') ;
        return [
            'status' => true,
            'message' => $message
        ];
    }

    public function assignVisitorToRoute($id, UpdateRequest $request)
    {

        $payload = $request->only($this->_fillable);

        $result = Visitors::_()->updateR($id, $payload);
        return [
            'status' => true,
            'message' => trans('messages.visitor.visitor.update'),
        ];
    }

    public function destroy(DestroyRequest $request)
    {
        $issuper=Visi::find($request->id);
        if($issuper->is_super_visitor){
        $count = OrderOrder::where('visitor_id', $request->id)->get()->count();
        if ($count)
            throw new CoreException("ویزیتور را نمی توان حذف کرد(ویزیتور سفارش ثبت کرده)");
        $result = Visitors::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.visitor.visitor.destroy'),
        ];
    }
    }
    public function destroy_super(DestroyRequest $request)
    {
        $issupers=Visi::whereIn($request->id);
      foreach($issupers as $issuper){
        if($issuper->is_super_visitor){
        $count = OrderOrder::where('visitor_id', $request->id)->get()->count();
        if ($count)
            throw new CoreException("ویزیتور را نمی توان حذف کرد(ویزیتور سفارش ثبت کرده)");
        $result = Visitors::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.visitor.visitor.destroy'),
        ];
    }
}
    }

    public function customers(Request $request)
    {

        ini_set('memory_limit', '512M');
        $visitor_id = $request->visitor_id;
        $id = $request->id;
        $name = $request->name;
        $visitor = Visitors::with('routes')->where('is_super_visitor', 0)->find($visitor_id);
        if (!$visitor) {
            throw new CoreException("ویزیتوری یافت نشد");
        }

        if (count($visitor->routes) > 0 and $visitor->routes[0]) {
            if (isset($visitor->routes[0]->id)) {

                $routes_id = $visitor->routes[0]->id;

                $cities = auth('api')->user()->Cities->pluck('id')->all();
                $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
                    ->select('users.*')
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
                        'routes' => function ($query) use ($routes_id) {
                            $query->where('id', $routes_id);
                        },
                        "PriceClasses",
                        'addresses',
                        'IntroducerCode',
                        'categories' => function ($query) {
                            $query->select('id', 'constant_fa');
                        },
                    ]);
                $customer = $customer->whereCities($cities);
                if ($id) {
                    $customer->where('id', $id);
                } elseif ($name) {
                    $customer->where(function ($query) use ($name) {
                        $query->where('first_name', 'like', '%' . $name . '%')->orWhere('last_name', 'like', '%' . $name . '%');
                    });
                }

                $customer = $customer->get();
                if (count($customer) < 1) {
                    throw new CoreException(' مشتری برای این ویزیتور یافت نشد');
                }
                return $customer;
            }
        } else {
            throw new CoreException("مسیری برای ویزتور ارسالی یافت نشد");
        }
    }

    public function unvisitedReport(UnvisitedReportRequest $request)
    {
        try {
            UnvisitedReport::create([
                'visitor_id' => $request['visitor_id'],
                'customer_id' => $request['customer_id'],
                'status' => $request['status'],
                'unvisited_description_id' => $request['unvisited_description_id'],
                'description' => $request['description'],
            ]);
            return [
                'statue' => true,
                'message' => 'رکورد با موفقیت ثبت شد.',
            ];
        } catch (\Exception $e) {
            dd($e->getMessage());
            return [
                'statue' => false,
                'message' => 'خطا در ثبت رکورد!',
            ];
        }
    }

    public function reason_for_not_visiting_list(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        $reasonForNotVisiting = ReasonForNotVisiting::select('*')->with([
            'visitor.Routes.RouteInfo',
            'customer.Routes.RouteInfo',
            'reson'
        ]);

        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $reasonForNotVisiting = $reasonForNotVisiting->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $companyId =  $request->company_id;
            $reasonForNotVisiting = $reasonForNotVisiting->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }


        if ($request->id)
            $reasonForNotVisiting->where('id', $request->id);
        if ($request->customer_id)
            $reasonForNotVisiting->where('customer_id', $request->customer_id);
        if ($request->description)
            $reasonForNotVisiting->where('description', 'like', '%' . $request->description . '%');

        if ($request->reson) {
            $reson = $request->reson;
            $reasonForNotVisiting->whereHas('reson', function ($q) use ($reson) {
                $q->where('message', 'like', '%' . $reson . '%');
            });
        }

        if ($request->visitor) {
            $visitor = $request->visitor;
            $reasonForNotVisiting->whereHas('visitor', function ($q) use ($visitor) {
                $q->where('first_name', 'like', '%' . $visitor . '%');
            });
        }
        if ($request->customer) {
            $customer = $request->customer;
            $reasonForNotVisiting->whereHas('customer', function ($q) use ($customer) {
                $q->where('first_name', 'like', '%' . $customer . '%');
            });
        }
        if ($request->routes) {
            $routes = $request->routes;
            $reasonForNotVisiting->whereHas('customer.Routes.RouteInfo', function ($q) use ($routes) {
                $q->where('route', 'like', '%' . $routes . '%');
            });
        }
        if ($request->daily) {
            $daily = Verta::parse($request->daily);
            $reasonForNotVisiting->whereDate('created_at', '=', $daily->DateTime()->format('Y-m-d'));
        }

        if ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $reasonForNotVisiting->whereDate('created_at', '>=', $from_date->DateTime()->format('Y-m-d'));
        }
        if ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $reasonForNotVisiting->whereDate('created_at', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        if ($request->created_at) {
            $ids = array_flip(array_flip(explode('|', $request->created_at)));
            $reasonForNotVisiting->whereBetween('created_at', $ids);
        }
        if ($request->sort)
            foreach ($request->sort as $key => $value) {
                if ($key == "visitor")
                    $reasonForNotVisiting->orderBy(Users::select('first_name')->whereColumn('users.id', 'reason_for_not_visitings.visitor_id'), $value);
                elseif ($key == "reson")
                    $reasonForNotVisiting->orderBy(NotVisited::select('message')->whereColumn('resone_not_visited.id', 'reason_for_not_visitings.reson_id'), $value);
                elseif ($key == "customer")
                    $reasonForNotVisiting->orderBy(Users::select('first_name')->whereColumn('users.id', 'reason_for_not_visitings.customer_id'), $value);
                elseif ($key == "routes");
                else
                    $reasonForNotVisiting->orderBy($key, $value);
            }
        else
            $reasonForNotVisiting->orderBy($sort, $order);
        return  $reasonForNotVisiting->jsonPaginate($limit);
    }
    public function reason_for_not_visiting_list_export(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {


        $result = array();
        $reson_ = ReasonForNotVisiting::select('*')->with('visitor.Routes.RouteInfo', 'customer.Route', 'customer.Routes.RouteInfo', 'reson');

        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $reson_->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $companyId =  $request->company_id;
            $reson_->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        if ($request->id)
            $reson_->where('id', $request->id);
        if ($request->customer_id)
            $reson_->where('customer_id', $request->customer_id);
        if ($request->description)
            $reson_->where('description', 'like', '%' . $request->description . '%');

        if ($request->reson) {
            $reson = $request->reson;
            $reson_->whereHas('reson', function ($q) use ($reson) {
                $q->where('message', 'like', '%' . $reson . '%');
            });
        }

        if ($request->visitor) {
            $visitor = $request->visitor;
            $reson_->whereHas('visitor', function ($q) use ($visitor) {
                $q->where('first_name', 'like', '%' . $visitor . '%');
            });
        }
        if ($request->customer) {
            $customer = $request->customer;
            $reson_->whereHas('customer', function ($q) use ($customer) {
                $q->where('first_name', 'like', '%' . $customer . '%');
            });
        }
        if ($request->routes) {
            $routes = $request->routes;
            $reson_->whereHas('customer.Routes.RouteInfo', function ($q) use ($routes) {
                $q->where('route', 'like', '%' . $routes . '%');
            });
        }
        if ($request->daily) {
            $daily = Verta::parse($request->daily);
            $reson_->whereDate('created_at', '=', $daily->DateTime()->format('Y-m-d'));
        }

        if ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $reson_->whereDate('created_at', '>=', $from_date->DateTime()->format('Y-m-d'));
        }
        if ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $reson_->whereDate('created_at', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        if ($request->created_at) {
            $ids = array_flip(array_flip(explode('|', $request->created_at)));
            $reson_->whereBetween('created_at', $ids);
        }
        if ($request->sort)
            foreach ($request->sort as $key => $value) {
                if ($key == "visitor")
                    $reson_->orderBy(Users::select('first_name')->whereColumn('users.id', 'reason_for_not_visitings.visitor_id'), $value);
                elseif ($key == "reson")
                    $reson_->orderBy(NotVisited::select('message')->whereColumn('resone_not_visited.id', 'reason_for_not_visitings.reson_id'), $value);
                elseif ($key == "customer")
                    $reson_->orderBy(Users::select('first_name')->whereColumn('users.id', 'reason_for_not_visitings.customer_id'), $value);
                else
                    $reson_->orderBy($key, $value);
            }
        else
            $reson_->orderBy($sort, $order);

        $reson_ = $reson_->get();
        foreach ($reson_ as $reson) {
            $fullname_visitor = isset($reson->visitor['first_name']) ? $reson->visitor['first_name'] : "";
            $fullname_visitor .= isset($reson->visitor['last_name']) ? $reson->visitor['last_name'] : "";
            $fullname_customer = isset($reson->customer['first_name']) ? $reson->customer['first_name'] : "";
            $fullname_customer .= isset($reson->customer['last_name']) ? $reson->customer['last_name'] : "";

            $result[] = [
                "0" => $reson->id,
                "1" => $reson->customer_id,
                "2" => $reson->description,
                "3" => (isset($reson->reson->message)) ? $reson->reson->message : "",
                "4" => $fullname_visitor,
                "5" => $fullname_customer,
                "6" => (isset($reson->customer->route[0]->route)) ? $reson->customer->route[0]->route : "",
                "7" => $reson->created_at,
            ];
        }



        $header = [
            "0" => 'شناسه',
            "1" => 'شناسه مشتری',
            "2" => 'توضیحات',
            "3" => 'دلیل',
            "4" => 'ویزیتور',
            "5" => 'مشتری',
            "6" => 'نام مسیر',
            "7" => 'تاریخ ایجاد',
        ];


        $excel = new Export($result, $header, 'export sheetName');
        return Excel::download($excel, 'Export file.xlsx');
    }

    public function reason_for_not_visiting_delete(ReasonForNotVisitingDeleteDestroyRequest $request)
    {
        $ids = $request->id;
        ReasonForNotVisiting::whereIn('id', $ids)->delete();

        return [
            'status' => true,
            'message' => 'با موفقیت حذف شد',
        ];
    }

    public function  listVisited(Request $request)
    {
        $all_tours = new Collection();

        $tours = TourVisitors::with(['Route.area', 'Visitor']);

        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $tours = $tours->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $companyId =  $request->company_id;
            $tours = $tours->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }


        if ($request->visitor_name) {
            $filter_visitor = $request->visitor_name;
            $tours->whereHas('visitor', function ($query) use ($filter_visitor) {
                $query->where('first_name', 'like', '%' . $filter_visitor . '%')->orwhere('last_name', 'like', '%' . $filter_visitor . '%');
            });
        }
        if ($request->visitors) {
            $filter_visitor = $request->visitors;
            $tours->whereHas('visitor', function ($query) use ($filter_visitor) {
                $query->whereIn('id', $filter_visitor);
            });
        }
        if ($request->routes) {
            $filter_route = $request->routes;
            $tours->whereHas('route', function ($query) use ($filter_route) {
                $query->where('route', 'like', '%' . $filter_route . '%');
            });
        }
        if ($request->area) {
            $filter_area = $request->area;
            $tours->whereHas('route.area', function ($query) use ($filter_area) {
                $query->where('area', 'like', '%' . $filter_area . '%');
            });
        }


        if ($request->from_date && $request->to_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
            $tours->whereBetween('date', [$from_date, $to_date]);
        } elseif ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $tours->whereDate('date', '>=', $from_date->DateTime()->format('Y-m-d'));
        } elseif ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $tours->whereDate('date', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        $tours = $tours->get();

        foreach ($tours as $tour) {
            $date = new DateTime($tour['date']);
            $date = $date->format('Y-m-d');
            $listCastomerInRoute = $this->listCastomerInRoute($tour->route_id);
            $getFirstTimeVisit = (isset($this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start)) ? $this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start : "";
            $getTotalCustomerInRoute = count($listCastomerInRoute);
            $getToTalCustomerisVisited = $this->getToTalCustomerisVisited($listCastomerInRoute, $tour->visitor_id, $date);
            $visited_is_success = $this->visited_is_success($listCastomerInRoute, $tour->visitor_id, $date);
            $getTotalTimeTour = $this->getTotalTimeTour($listCastomerInRoute, $tour->visitor_id, $date);
            $temp_tour['id'] = $tour['id'];
            $temp_tour['visitor'] = $tour['visitor'];
            $temp_tour['route'] = $tour['route'];
            $temp_tour['area'] = (isset($tour['route']['area'])) ? $tour['route']['area'] : '';
            $temp_tour['start_time_visit'] = $getFirstTimeVisit;
            $temp_tour['date'] = $date;
            $temp_tour['end_time_visit'] = (isset($this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end)) ? $this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end : "";
            $temp_tour['total_customer'] = ($getTotalCustomerInRoute) ? $getTotalCustomerInRoute : "0";
            $temp_tour['total_customers_visited'] = ($getToTalCustomerisVisited) ? $getToTalCustomerisVisited : "0";
            $temp_tour['total_customers_is_not_visited'] = ($getTotalCustomerInRoute - $getToTalCustomerisVisited) ? $getTotalCustomerInRoute - $getToTalCustomerisVisited : "0";
            $temp_tour['visited_is_success'] = (!empty($visited_is_success)) ? $visited_is_success : "0";
            $temp_tour['visited_is_not_success'] = ($getToTalCustomerisVisited - $visited_is_success) ? $getToTalCustomerisVisited - $visited_is_success : "0";
            $temp_tour['avarage_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", ($getTotalTimeTour / $getToTalCustomerisVisited)) : "0";
            //$temp_tour['details'] = $this->getToTalCustomerinDetailsTime($listCastomerInRoute, $tour->visitor_id,  $date, $tour->route_id);
            $temp_tour['total_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", $getTotalTimeTour) : "0";
            $tem = new Collection($temp_tour);
            $all_tours->push($tem);
            $temp_tour = array();
        }


        if ($request->number_all_customer)
            $all_tours = $all_tours->where('total_customer', $request->number_all_customer);
        if ($request->total_customers_visited)
            $all_tours = $all_tours->where('total_customers_visited', $request->total_customers_visited);
        if ($request->total_customers_is_not_visited)
            $all_tours = $all_tours->where('total_customers_is_not_visited', $request->total_customers_is_not_visited);
        if ($request->visited_is_success)
            $all_tours = $all_tours->where('visited_is_success', $request->visited_is_success);
        if ($request->visited_is_not_success)
            $all_tours = $all_tours->where('visited_is_not_success', $request->visited_is_not_success);

        if (isset($request->sort['total_customers_is_not_visited'])) {
            if ($request->sort['total_customers_is_not_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_is_not_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_is_not_visited');
        } elseif (isset($request->sort['total_time'])) {
            if ($request->sort['total_time'] == 'asc')
                $all_tours = $all_tours->sortBy('total_time');
            else
                $all_tours = $all_tours->SortByDesc('total_time');
        } elseif (isset($request->sort['details'])) {
            if ($request->sort['details'] == 'asc')
                $all_tours = $all_tours->sortBy('details');
            else
                $all_tours = $all_tours->SortByDesc('details');
        } elseif (isset($request->sort['visited_is_not_success'])) {
            if ($request->sort['visited_is_not_success'] == 'asc')
                $all_tours = $all_tours->sortBy('visited_is_not_success');
            else
                $all_tours = $all_tours->SortByDesc('visited_is_not_success');
        } elseif (isset($request->sort['total_customers_visited'])) {
            if ($request->sort['total_customers_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_visited');
        } elseif (isset($request->sort['number_all_customer'])) {
            if ($request->sort['number_all_customer'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customer');
            else
                $all_tours = $all_tours->SortByDesc('total_customer');
        } elseif (isset($request->sort['end_time_visit'])) {
            if ($request->sort['end_time_visit'] == 'asc')
                $all_tours = $all_tours->sortBy('end_time_visit');
            else
                $all_tours = $all_tours->SortByDesc('end_time_visit');
        } else {
            $all_tours = $all_tours->SortByDesc('end_time_visit');
        }
        $test = $all_tours->all();
        $data = $this->paginate($test, $request->page['size'], $request->page['number']);
        return $data;
    }

    /* public function  listVisitedExcel(Request $request)
    {
        $all_tours = new Collection();

        $tours = TourVisitors::with(['Route.area', 'Visitor']);
        if ($request->visitor_name) {
            $filter_visitor = $request->visitor_name;
            $tours->whereHas('visitor', function ($query) use ($filter_visitor) {
                $query->where('first_name', 'like', '%' . $filter_visitor . '%')->orwhere('last_name', 'like', '%' . $filter_visitor . '%');
            });
        }
        if ($request->routes) {
            $filter_route = $request->routes;
            $tours->whereHas('route', function ($query) use ($filter_route) {
                $query->where('route', 'like', '%' . $filter_route . '%');
            });
        }
        if ($request->area) {
            $filter_area = $request->area;
            $tours->whereHas('route.area', function ($query) use ($filter_area) {
                $query->where('area', 'like', '%' . $filter_area . '%');
            });
        }


        if ($request->from_date && $request->to_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
            $tours->whereBetween('date', [$from_date, $to_date]);
        } elseif ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $tours->whereDate('date', '>=', $from_date->DateTime()->format('Y-m-d'));
        } elseif ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $tours->whereDate('date', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        $tours = $tours->get();

        foreach ($tours as $tour) {
            $date = new DateTime($tour['date']);
            $date = $date->format('Y-m-d');
            $listCastomerInRoute = $this->listCastomerInRoute($tour->route_id);
            $getFirstTimeVisit = (isset($this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start)) ? $this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start : "";
            $getTotalCustomerInRoute = count($listCastomerInRoute);
            $getToTalCustomerisVisited = $this->getToTalCustomerisVisited($listCastomerInRoute, $tour->visitor_id, $date);
            $visited_is_success = $this->visited_is_success($listCastomerInRoute, $tour->visitor_id, $date);
            $getTotalTimeTour = $this->getTotalTimeTour($listCastomerInRoute, $tour->visitor_id, $date);
            $temp_tour['id'] = $tour['id'];
            $temp_tour['route'] = (isset($tour['route']['route'])) ? $tour['route']['route'] : "";
            $temp_tour['visitor'] = $tour['visitor']['full_name'];
            $temp_tour['start_time_visit'] = $getFirstTimeVisit;
            $temp_tour['end_time_visit'] = (isset($this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end)) ? $this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end : "";
            $temp_tour['total_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", $getTotalTimeTour) : "0";
            $temp_tour['avarage_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", ($getTotalTimeTour / $getToTalCustomerisVisited)) : "0";
            $temp_tour['date'] = $date;
            $temp_tour['total_customer'] = ($getTotalCustomerInRoute) ? $getTotalCustomerInRoute : "0";
            $temp_tour['total_customers_visited'] = ($getToTalCustomerisVisited) ? $getToTalCustomerisVisited : "0";
            $temp_tour['total_customers_is_not_visited'] = ($getTotalCustomerInRoute - $getToTalCustomerisVisited) ? $getTotalCustomerInRoute - $getToTalCustomerisVisited : "0";
            $temp_tour['visited_is_success'] = (!empty($visited_is_success)) ? $visited_is_success : "0";
            $temp_tour['visited_is_not_success'] = ($getToTalCustomerisVisited - $visited_is_success) ? $getToTalCustomerisVisited - $visited_is_success : "0";
            $tem = new Collection($temp_tour);
            $all_tours->push($tem);
            $temp_tour = array();
        }





        if ($request->number_all_customer)
            $all_tours = $all_tours->where('total_customer', $request->number_all_customer);
        if ($request->total_customers_visited)
            $all_tours = $all_tours->where('total_customers_visited', $request->total_customers_visited);
        if ($request->total_customers_is_not_visited)
            $all_tours = $all_tours->where('total_customers_is_not_visited', $request->total_customers_is_not_visited);
        if ($request->visited_is_success)
            $all_tours = $all_tours->where('visited_is_success', $request->visited_is_success);
        if ($request->visited_is_not_success)
            $all_tours = $all_tours->where('visited_is_not_success', $request->visited_is_not_success);


        if (isset($request->sort['total_customers_is_not_visited'])) {
            if ($request->sort['total_customers_is_not_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_is_not_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_is_not_visited');
        }
        if (isset($request->sort['total_time'])) {
            if ($request->sort['total_time'] == 'asc')
                $all_tours = $all_tours->sortBy('total_time');
            else
                $all_tours = $all_tours->SortByDesc('total_time');
        }
        if (isset($request->sort['details'])) {
            if ($request->sort['details'] == 'asc')
                $all_tours = $all_tours->sortBy('details');
            else
                $all_tours = $all_tours->SortByDesc('details');
        }
        if (isset($request->sort['visited_is_not_success'])) {
            if ($request->sort['visited_is_not_success'] == 'asc')
                $all_tours = $all_tours->sortBy('visited_is_not_success');
            else
                $all_tours = $all_tours->SortByDesc('visited_is_not_success');
        }
        if (isset($request->sort['total_customers_visited'])) {
            if ($request->sort['total_customers_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_visited');
        }
        if (isset($request->sort['number_all_customer'])) {
            if ($request->sort['number_all_customer'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customer');
            else
                $all_tours = $all_tours->SortByDesc('total_customer');
        }
        if (isset($request->sort['end_time_visit'])) {
            if ($request->sort['end_time_visit'] == 'asc')
                $all_tours = $all_tours->sortBy('end_time_visit');
            else
                $all_tours = $all_tours->SortByDesc('end_time_visit');
        }
        $data = $all_tours->all();
        $header = [
            "0" => 'شناسه',
            "1" => 'نام مسیر',
            "2" => 'نام ویزیتور',
            "3" => 'شروع ویزیت',
            "4" => 'پایان وزیت',
            "5" => 'زمان کل ویزیت مسیر',
            "6" => 'زمان میانگین هر مشتری',
            "7" => 'تاریخ ثبت',
            "8" => 'تعداد کل مشتریان',
            "9" => 'تعداد مشتریان ویزیت شده',
            "10" => 'تعداد مشتریان ویزیت نشده',
            "11" => 'تعداد ویزیت موفق ',
            "12" => 'تعداد وزیت ناموفق',
        ];


        $excel = new Export($data, $header, 'export sheetName');
        return Excel::download($excel, 'Export file.xlsx');
    }*/


    public function  listVisitedExcel2(Request $request)
    {

        $mergeRowData = $this->getClomenMergeHeader();
        $start = 3;
        $all_tours = new Collection();
        $sub_header = [
            "H1" => '',
            "H2" => '',
            "H3" => '',
            "H4" => '',
            "H5" => '',
            "H6" => '',
            "H7" => '',
            "H8" => '',
            "H9" => '',
            "H10" => '',
            "H11" => '',
            "H12" => '',
            "H27" => '',
            "H13" => 'شناسه ی مشتری',
            "H14" => 'نام مشتری',
            "H15" => 'شروع  ویزیت مشتری',
            "H16" => 'پایان ویزیت مشتری',
            "H17" => 'مدت زمان ویزیت',
            "H27" => 'تعداد ویزیت',
            "H18" => 'تعداد کالا ها',
            "H19" => 'شناسه ی سفارش',
            "H20" => 'مبلغ',
            "H21" => 'دلیل عدم سفارش',
            "H22" => '  عرض جغرافیایی مشتری',
            "H23" => 'طول جغرافیایی مشتری ',
            "H24" => 'وضعیت',
            "H25" => '  عرض جغرافیایی ویزیت',
            "H26" => 'طول جغرافیایی ویزیت ',
        ];
        $all_tours->push(new Collection($sub_header));


        $tours = TourVisitors::with(['Route.area', 'Visitor']);
        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $tours = $tours->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $companyId =  $request->company_id;
            $tours = $tours->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }
        if ($request->visitor_name) {
            $filter_visitor = $request->visitor_name;
            $tours->whereHas('visitor', function ($query) use ($filter_visitor) {
                $query->where('first_name', 'like', '%' . $filter_visitor . '%')->orwhere('last_name', 'like', '%' . $filter_visitor . '%');
            });
        }
        if ($request->routes) {
            $filter_route = $request->routes;
            $tours->whereHas('route', function ($query) use ($filter_route) {
                $query->where('route', 'like', '%' . $filter_route . '%');
            });
        }
        if ($request->area) {
            $filter_area = $request->area;
            $tours->whereHas('route.area', function ($query) use ($filter_area) {
                $query->where('area', 'like', '%' . $filter_area . '%');
            });
        }


        if ($request->from_date && $request->to_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
            $tours->whereBetween('date', [$from_date, $to_date]);
        } elseif ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $tours->whereDate('date', '>=', $from_date->DateTime()->format('Y-m-d'));
        } elseif ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $tours->whereDate('date', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        $tours = $tours->get();

        foreach ($tours as $key => $tour) {
            $date = new DateTime($tour['date']);
            $date = $date->format('Y-m-d');
            $date_jalali = new verta1($date);
            $detalisTemp = array();
            $temp_tour = array();
            $listCastomerInRoute = $this->listCastomerInRoute($tour->route_id);
            $getFirstTimeVisit = (isset($this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start)) ? new Verta1($this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start) : null;
            $getlastTimeVisit = (isset($this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end)) ? new Verta1($this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end) : null;
            $getTotalCustomerInRoute = count($listCastomerInRoute);
            $getToTalCustomerisVisited = $this->getToTalCustomerisVisited($listCastomerInRoute, $tour->visitor_id, $date);
            $visited_is_success = $this->visited_is_success($listCastomerInRoute, $tour->visitor_id, $date);
            $getTotalTimeTour = $this->getTotalTimeTour($listCastomerInRoute, $tour->visitor_id, $date);
            $temp_tour['id'] = $tour['id'];
            $temp_tour['route'] = (isset($tour['route']['route'])) ? $tour['route']['route'] : "";
            $temp_tour['visitor'] = $tour['visitor']['full_name'];
            $temp_tour['start_time_visit'] = ($getFirstTimeVisit) ? str_replace('-', '/', $getFirstTimeVisit->formatDate()) : "0";
            $temp_tour['end_time_visit'] = ($getlastTimeVisit) ? str_replace('-', '/', $getlastTimeVisit->formatDate()) : "0";
            $temp_tour['total_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", $getTotalTimeTour) : "0";
            $temp_tour['avarage_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", ($getTotalTimeTour / $getToTalCustomerisVisited)) : "0";
            $temp_tour['date'] = str_replace('-', '/', $date_jalali->formatDate());
            $temp_tour['total_customer'] = ($getTotalCustomerInRoute) ? $getTotalCustomerInRoute : "0";
            $temp_tour['total_customers_visited'] = ($getToTalCustomerisVisited) ? $getToTalCustomerisVisited : "0";
            $temp_tour['total_customers_is_not_visited'] = ($getTotalCustomerInRoute - $getToTalCustomerisVisited) ? $getTotalCustomerInRoute - $getToTalCustomerisVisited : "0";
            $temp_tour['visited_is_success'] = (!empty($visited_is_success)) ? $visited_is_success : "0";
            $temp_tour['visited_is_not_success'] = ($getToTalCustomerisVisited - $visited_is_success) ? $getToTalCustomerisVisited - $visited_is_success : "0";
            if ($getTotalCustomerInRoute) {
                $detalisTemp = $this->getToTalCustomerinDetails($tour->visitor_id, $date, $tour->route_id);

                if (isset($detalisTemp[0])) {
                    $count_visited = count($detalisTemp[0]['visit_time']);
                    $times = 0;
                    foreach ($detalisTemp[0]['visit_time'] as $timet) {
                        $times += $timet['time_visited'];
                    }

                    //   $temp_tour1['0count_time'] = "" . $count_visited;

                    $temp_tour['customer_id'] = $detalisTemp[0]['customer_id'];
                    $temp_tour['0name'] = $detalisTemp[0]['full_name'];
                    $temp_tour['0start'] = (isset($detalisTemp[0]['visit_time'][0])) ?  $this->getTime($detalisTemp[0]['visit_time'][0]['time_start']) : "0";
                    $temp_tour['0end'] = (isset($detalisTemp[0]['visit_time'][0])) ? $this->getTime($detalisTemp[0]['visit_time'][0]['time_end']) : "0";
                    $temp_tour['0time'] = (isset($detalisTemp[0]['visit_time'][0])) ? gmdate("H:i:s", $times) : "0";
                    $temp_tour['num_product'] = $detalisTemp[0]['num_product'] . "";
                    $temp_tour['order_id'] = $detalisTemp[0]['order_id'] . "";
                    $temp_tour['price'] = number_format($detalisTemp[0]['final_price']) . "";
                    $temp_tour['reson'] = $detalisTemp[0]['reson_for_not_visiting'] . "";
                    $temp_tour['lat'] = $detalisTemp[0]['lat'] . "";
                    $temp_tour['long'] = $detalisTemp[0]['long'] . "";
                    $temp_tour['status'] = $detalisTemp[0]['status'] . "";
                    $temp_tour['lat_visit'] =  (isset($detalisTemp[0]['visit_time'][$count_visited - 1])) ? $detalisTemp[0]['visit_time'][$count_visited - 1]['lat'] . "" : "";
                    $temp_tour['long_visit'] = (isset($detalisTemp[0]['visit_time'][$count_visited - 1])) ? $detalisTemp[0]['visit_time'][$count_visited - 1]['lat'] . "" : "";
                }
            }
            $tem = new Collection($temp_tour);
            $all_tours->push($tem);

            $temp_tour1 = array();
            for ($i = 1; $i < $getTotalCustomerInRoute; $i++) {
                $temp_tour1[$i . "id"] = $i;
                $temp_tour1[$i . "route"] =  $i;
                $temp_tour1[$i . "visitor"] = $i;
                $temp_tour1[$i . "start_time_visit"] = $i;
                $temp_tour1[$i . "end_time_visit"] =  $i;
                $temp_tour1[$i . "total_time"] =  $i;
                $temp_tour1[$i . "avarage_time"] =  $i;
                $temp_tour1[$i . "date"] = "";
                $temp_tour1[$i . "total_customer"] =  "";
                $temp_tour1[$i . "total_customers_visited"] =  "";
                $temp_tour1[$i . "total_customers_is_not_visited"] =  "";
                $temp_tour1[$i . "visited_is_success"] =  "";
                $temp_tour1[$i . "visited_is_not_success"] =  "";
                if (isset($detalisTemp[$i])) {
                    $count_visited = count($detalisTemp[$i]['visit_time']);
                    $times = 0;
                    foreach ($detalisTemp[$i]['visit_time'] as $timet) {
                        $times += $timet['time_visited'];
                    }
                    $temp_tour1['customer_id'] = $detalisTemp[$i]['customer_id'];
                    $temp_tour1['0name'] = $detalisTemp[$i]['full_name'];
                    $temp_tour1['0start'] = (isset($detalisTemp[$i]['visit_time'][0])) ?  $this->getTime($detalisTemp[$i]['visit_time'][0]['time_start']) : "0";
                    $temp_tour1['0end'] = (isset($detalisTemp[$i]['visit_time'][0])) ? $this->getTime($detalisTemp[$i]['visit_time'][0]['time_end']) : "0";
                    $temp_tour1['0time'] = (isset($detalisTemp[$i]['visit_time'][0])) ? gmdate("H:i:s", $detalisTemp[$i]['visit_time'][0]['time_visited']) : "0";
                    $temp_tour1['num_product'] = $detalisTemp[$i]['num_product'] . "";
                    $temp_tour1['order_id'] = $detalisTemp[$i]['order_id'] . "";
                    $temp_tour1['price'] = number_format($detalisTemp[$i]['final_price']) . "";
                    $temp_tour1['reson'] = $detalisTemp[$i]['reson_for_not_visiting'] . "";
                    $temp_tour1['lat'] = $detalisTemp[$i]['lat'] . "";
                    $temp_tour1['long'] = $detalisTemp[$i]['long'] . "";
                    $temp_tour1['status'] = $detalisTemp[$i]['status'] . "";
                    $temp_tour1['lat_visit'] =  (isset($detalisTemp[0]['visit_time'][$count_visited - 1])) ? $detalisTemp[$i]['visit_time'][$count_visited - 1]['lat'] . "" : "";
                    $temp_tour1['long_visit'] = (isset($detalisTemp[0]['visit_time'][$count_visited - 1])) ? $detalisTemp[$i]['visit_time'][$count_visited - 1]['lat'] . "" : "";
                } else {
                    $temp_tour1['0name'] = "پیدا نشد";
                    $temp_tour1['0start'] = "پیدا نشد";
                    $temp_tour1['0end']  = "پیدا نشد";
                    $temp_tour1['0time'] = "پیدا نشد";
                    $temp_tour1['num_product'] = "پیدا نشد";
                    $temp_tour1['order_id'] = "پیدا نشد";
                    $temp_tour1['price'] = "پیدا نشد";
                    $temp_tour1['reson'] = "پیدا نشد";
                    $temp_tour1['lat'] =  "";
                    $temp_tour1['long'] =  "";
                    $temp_tour1['status'] = "";
                    $temp_tour1['lat_visit'] =  "";
                    $temp_tour1['long_visit'] =  "";
                }
                $tem1 = new Collection($temp_tour1);
                $all_tours->push($tem1);
                $temp_tour1 = array();
            }


            if ($getTotalCustomerInRoute > 1) {
                $end = ($getTotalCustomerInRoute - 1) + $start;
                array_push($mergeRowData, "A" . $start . ":A" . $end);
                array_push($mergeRowData, "B" . $start . ":B" . $end);
                array_push($mergeRowData, "C" . $start . ":C" . $end);
                array_push($mergeRowData, "D" . $start . ":D" . $end);
                array_push($mergeRowData, "E" . $start . ":E" . $end);
                array_push($mergeRowData, "F" . $start . ":F" . $end);
                array_push($mergeRowData, "G" . $start . ":G" . $end);
                array_push($mergeRowData, "H" . $start . ":H" . $end);
                array_push($mergeRowData, "I" . $start . ":I" . $end);
                array_push($mergeRowData, "J" . $start . ":J" . $end);
                array_push($mergeRowData, "K" . $start . ":K" . $end);
                array_push($mergeRowData, "L" . $start . ":L" . $end);
                array_push($mergeRowData, "M" . $start . ":M" . $end);
                $start = $end + 1;
            } else $start += 1;
        }





        if ($request->number_all_customer)
            $all_tours = $all_tours->where('total_customer', $request->number_all_customer);
        if ($request->total_customers_visited)
            $all_tours = $all_tours->where('total_customers_visited', $request->total_customers_visited);
        if ($request->total_customers_is_not_visited)
            $all_tours = $all_tours->where('total_customers_is_not_visited', $request->total_customers_is_not_visited);
        if ($request->visited_is_success)
            $all_tours = $all_tours->where('visited_is_success', $request->visited_is_success);
        if ($request->visited_is_not_success)
            $all_tours = $all_tours->where('visited_is_not_success', $request->visited_is_not_success);


        if (isset($request->sort['total_customers_is_not_visited'])) {
            if ($request->sort['total_customers_is_not_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_is_not_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_is_not_visited');
        } elseif (isset($request->sort['total_time'])) {
            if ($request->sort['total_time'] == 'asc')
                $all_tours = $all_tours->sortBy('total_time');
            else
                $all_tours = $all_tours->SortByDesc('total_time');
        } elseif (isset($request->sort['details'])) {
            if ($request->sort['details'] == 'asc')
                $all_tours = $all_tours->sortBy('details');
            else
                $all_tours = $all_tours->SortByDesc('details');
        } elseif (isset($request->sort['visited_is_not_success'])) {
            if ($request->sort['visited_is_not_success'] == 'asc')
                $all_tours = $all_tours->sortBy('visited_is_not_success');
            else
                $all_tours = $all_tours->SortByDesc('visited_is_not_success');
        } elseif (isset($request->sort['total_customers_visited'])) {
            if ($request->sort['total_customers_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_visited');
        } elseif (isset($request->sort['number_all_customer'])) {
            if ($request->sort['number_all_customer'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customer');
            else
                $all_tours = $all_tours->SortByDesc('total_customer');
        } elseif (isset($request->sort['end_time_visit'])) {
            if ($request->sort['end_time_visit'] == 'asc')
                $all_tours = $all_tours->sortBy('end_time_visit');
            else
                $all_tours = $all_tours->SortByDesc('end_time_visit');
        } else {
            // $all_tours = $all_tours->SortByDesc('end_time_visit');
        }
        $header = [
            "0" => 'شناسه',
            "1" => 'نام مسیر',
            "2" => 'نام ویزیتور',
            "3" => 'شروع ویزیت',
            "4" => 'پایان وزیت',
            "5" => 'زمان کل ویزیت مسیر',
            "6" => 'زمان میانگین هر مشتری',
            "7" => 'تاریخ ثبت',
            "8" => 'تعداد کل مشتریان',
            "9" => 'تعداد مشتریان ویزیت شده',
            "10" => 'تعداد مشتریان ویزیت نشده',
            "11" => 'تعداد ویزیت موفق ',
            "12" => 'تعداد وزیت ناموفق',
            "13" => 'جزئیات مشتری',
        ];
        $data = $all_tours->all();

        $excel = new Export($data, $header, 'export sheetName');

        $excel->setMergeCells($mergeRowData);
        return Excel::download($excel, 'Export file.xlsx');
    }
    public function  listVisitedExcel(Request $request)
    {

        $all_tours = new Collection();

        $tours = TourVisitors::with(['Route.area', 'Visitor']);
        if (auth('api')->user()->kind == 'company') {
            $companyId = auth('api')->user()->company_id;
            $tours = $tours->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        } else {
            if (!$request->has('company_id'))
                throw new CoreException("شناسه ی کمپانی الزامیست");
            $companyId =  $request->company_id;
            $tours = $tours->whereHas('visitor', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }


        if ($request->visitor_name) {
            $filter_visitor = $request->visitor_name;
            $tours->whereHas('visitor', function ($query) use ($filter_visitor) {
                $query->where('first_name', 'like', '%' . $filter_visitor . '%')->orwhere('last_name', 'like', '%' . $filter_visitor . '%');
            });
        }
        if ($request->visitors) {
            $filter_visitor_ids = $request->visitors;
            $tours->whereHas('visitor', function ($query) use ($filter_visitor_ids) {
                $query->where('id', $filter_visitor_ids);
            });
        }
        if ($request->routes) {
            $filter_route = $request->routes;
            $tours->whereHas('route', function ($query) use ($filter_route) {
                $query->where('route', 'like', '%' . $filter_route . '%');
            });
        }
        if ($request->area) {
            $filter_area = $request->area;
            $tours->whereHas('route.area', function ($query) use ($filter_area) {
                $query->where('area', 'like', '%' . $filter_area . '%');
            });
        }


        if ($request->from_date && $request->to_date) {
            $from_date = Verta::parse($request->from_date);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
            $to_date = Verta::parse($request->to_date);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
            $tours->whereBetween('date', [$from_date, $to_date]);
        } elseif ($request->from_date) {
            $from_date = Verta::parse($request->from_date);
            $tours->whereDate('date', '>=', $from_date->DateTime()->format('Y-m-d'));
        } elseif ($request->to_date) {
            $to_date = Verta::parse($request->to_date);
            $tours->whereDate('date', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        $tours = $tours->get();

        foreach ($tours as $key => $tour) {
            $date = new DateTime($tour['date']);
            $date = $date->format('Y-m-d');
            $date_jalali = new verta1($date);
            $detalisTemp = array();
            $temp_tour = array();
            $listCastomerInRoute = $this->listCastomerInRoute($tour->route_id);
            $getFirstTimeVisit = (isset($this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start)) ? new Verta1($this->getFirstTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_start) : null;
            $getlastTimeVisit = (isset($this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end)) ? new Verta1($this->getlastTimeVisit($listCastomerInRoute, $tour->visitor_id, $date)->time_end) : null;
            $getTotalCustomerInRoute = count($listCastomerInRoute);
            $getToTalCustomerisVisited = $this->getToTalCustomerisVisited($listCastomerInRoute, $tour->visitor_id, $date);
            $visited_is_success = $this->visited_is_success($listCastomerInRoute, $tour->visitor_id, $date);
            $getTotalTimeTour = $this->getTotalTimeTour($listCastomerInRoute, $tour->visitor_id, $date);
            if ($getTotalCustomerInRoute) {
                $detalisTemp = $this->getToTalCustomerinDetailsdablicate($tour->visitor_id, $date, $tour->route_id);
            }

            $temp_tour1 = array();
            for ($i = 0; $i < $getTotalCustomerInRoute; $i++) {

                $temp_tour1['id'] = $tour['id'];
                $temp_tour1['route'] = (isset($tour['route']['route'])) ? $tour['route']['route'] : "";
                $temp_tour1['visitor'] = $tour['visitor']['full_name'];
                $temp_tour1['start_time_visit'] = ($getFirstTimeVisit) ? str_replace('-', '/', $getFirstTimeVisit->formatDate()) : "0";
                $temp_tour1['end_time_visit'] = ($getlastTimeVisit) ? str_replace('-', '/', $getlastTimeVisit->formatDate()) : "0";
                $temp_tour1['total_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", $getTotalTimeTour) : "0";
                $temp_tour1['avarage_time'] = ($getTotalTimeTour) ? gmdate("H:i:s", ($getTotalTimeTour / $getToTalCustomerisVisited)) : "0";
                $temp_tour1['date'] = str_replace('-', '/', $date_jalali->formatDate());
                $temp_tour1['total_customer'] = ($getTotalCustomerInRoute) ? $getTotalCustomerInRoute : "0";
                $temp_tour1['total_customers_visited'] = ($getToTalCustomerisVisited) ? $getToTalCustomerisVisited : "0";
                $temp_tour1['total_customers_is_not_visited'] = ($getTotalCustomerInRoute - $getToTalCustomerisVisited) ? $getTotalCustomerInRoute - $getToTalCustomerisVisited : "0";
                $temp_tour1['visited_is_success'] = (!empty($visited_is_success)) ? $visited_is_success : "0";
                $temp_tour1['visited_is_not_success'] = ($getToTalCustomerisVisited - $visited_is_success) ? $getToTalCustomerisVisited - $visited_is_success : "0";
                if (isset($detalisTemp[$i])) {
                    $count_visited = count($detalisTemp[$i]['visit_time']);
                    $times = 0;
                    foreach ($detalisTemp[$i]['visit_time'] as $timet) {
                        $times += $timet['time_visited'];
                    }
                    $temp_tour1['customer_id'] = $detalisTemp[$i]['customer_id'];
                    $temp_tour1['0name'] = $detalisTemp[$i]['full_name'];
                    $temp_tour1['0start'] = (isset($detalisTemp[$i]['visit_time'][0])) ?  $this->getTime($detalisTemp[$i]['visit_time'][0]['time_start']) : "0";
                    $temp_tour1['0end'] = (isset($detalisTemp[$i]['visit_time'][0])) ? $this->getTime($detalisTemp[$i]['visit_time'][$count_visited - 1]['time_end']) : "0";
                    $temp_tour1['0time'] = (isset($detalisTemp[$i]['visit_time'][0])) ? gmdate("H:i:s", $times) : "0";
                    $temp_tour1['0count_time'] = "" . $count_visited;
                    $temp_tour1['num_product'] = $detalisTemp[$i]['num_product'] . "";
                    $temp_tour1['order_id'] = $detalisTemp[$i]['order_id'] . "";
                    $temp_tour1['price'] = number_format($detalisTemp[$i]['final_price']);
                    $temp_tour1['reson'] = $detalisTemp[$i]['reson_for_not_visiting'] . "";
                    $temp_tour1['lat'] = $detalisTemp[$i]['lat'] . "";
                    $temp_tour1['long'] = $detalisTemp[$i]['long'] . "";
                    $temp_tour1['status'] = $detalisTemp[$i]['status'] . "";
                    $temp_tour1['lat_visit'] = (isset($detalisTemp[$i]['visit_time'][0])) ? $detalisTemp[$i]['visit_time'][$count_visited - 1]['lat'] : "";
                    $temp_tour1['long_visit'] = (isset($detalisTemp[$i]['visit_time'][0])) ? $detalisTemp[$i]['visit_time'][$count_visited - 1]['long'] : "";
                } else {
                    $temp_tour1['0name'] = "پیدا نشد";
                    $temp_tour1['0start'] = "پیدا نشد";
                    $temp_tour1['0end']  = "پیدا نشد";
                    $temp_tour1['0time'] = "پیدا نشد";
                    $temp_tour1['num_product'] = "پیدا نشد";
                    $temp_tour1['order_id'] = "پیدا نشد";
                    $temp_tour1['price'] = "پیدا نشد";
                    $temp_tour1['reson'] = "پیدا نشد";
                    $temp_tour1['lat'] =  "";
                    $temp_tour1['long'] =  "";
                    $temp_tour1['status'] = "";
                }
                $tem1 = new Collection($temp_tour1);
                $all_tours->push($tem1);
                $temp_tour1 = array();
            }
        }





        if ($request->number_all_customer)
            $all_tours = $all_tours->where('total_customer', $request->number_all_customer);
        if ($request->total_customers_visited)
            $all_tours = $all_tours->where('total_customers_visited', $request->total_customers_visited);
        if ($request->total_customers_is_not_visited)
            $all_tours = $all_tours->where('total_customers_is_not_visited', $request->total_customers_is_not_visited);
        if ($request->visited_is_success)
            $all_tours = $all_tours->where('visited_is_success', $request->visited_is_success);
        if ($request->visited_is_not_success)
            $all_tours = $all_tours->where('visited_is_not_success', $request->visited_is_not_success);


        if (isset($request->sort['total_customers_is_not_visited'])) {
            if ($request->sort['total_customers_is_not_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_is_not_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_is_not_visited');
        } elseif (isset($request->sort['total_time'])) {
            if ($request->sort['total_time'] == 'asc')
                $all_tours = $all_tours->sortBy('total_time');
            else
                $all_tours = $all_tours->SortByDesc('total_time');
        } elseif (isset($request->sort['details'])) {
            if ($request->sort['details'] == 'asc')
                $all_tours = $all_tours->sortBy('details');
            else
                $all_tours = $all_tours->SortByDesc('details');
        } elseif (isset($request->sort['visited_is_not_success'])) {
            if ($request->sort['visited_is_not_success'] == 'asc')
                $all_tours = $all_tours->sortBy('visited_is_not_success');
            else
                $all_tours = $all_tours->SortByDesc('visited_is_not_success');
        } elseif (isset($request->sort['total_customers_visited'])) {
            if ($request->sort['total_customers_visited'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customers_visited');
            else
                $all_tours = $all_tours->SortByDesc('total_customers_visited');
        } elseif (isset($request->sort['number_all_customer'])) {
            if ($request->sort['number_all_customer'] == 'asc')
                $all_tours = $all_tours->sortBy('total_customer');
            else
                $all_tours = $all_tours->SortByDesc('total_customer');
        } elseif (isset($request->sort['end_time_visit'])) {
            if ($request->sort['end_time_visit'] == 'asc')
                $all_tours = $all_tours->sortBy('end_time_visit');
            else
                $all_tours = $all_tours->SortByDesc('end_time_visit');
        } else {
            $all_tours = $all_tours->SortByDesc('end_time_visit');
        }
        $header = [
            "0" => 'شناسه',
            "1" => 'نام مسیر',
            "2" => 'نام ویزیتور',
            "3" => 'شروع ویزیت کل مسیر',
            "4" => 'پایان ویزیت کل مسیر',
            "5" => 'زمان کل ویزیت مسیر',
            "6" => 'زمان میانگین هر مشتری',
            "7" => 'تاریخ ثبت',
            "8" => 'تعداد کل مشتریان',
            "9" => 'تعداد مشتریان ویزیت شده',
            "10" => 'تعداد مشتریان ویزیت نشده',
            "11" => 'تعداد ویزیت موفق ',
            "12" => 'تعداد وزیت ناموفق',
            "H13" => 'شناسه ی مشتری',
            "H14" => 'نام مشتری',
            "H15" => 'شروع  ویزیت مشتری',
            "H16" => 'پایان ویزیت مشتری',
            "H17" => 'مدت زمان ویزیت',
            "H27" => 'تعداد ویزیت در روز',
            "H18" => 'تعداد کالا ها',
            "H19" => 'شناسه ی سفارش',
            "H20" => 'مبلغ',
            "H21" => 'دلیل عدم سفارش',
            "H22" => '  عرض جغرافیایی مشتری',
            "H23" => 'طول جغرافیایی مشتری ',
            "H24" => 'وضعیت',
            "H25" => '  عرض جغرافیایی ویزیت',
            "H26" => 'طول جغرافیایی ویزیت ',
        ];


        $data = $all_tours->all();


        $excel = new Export($data, $header, 'export sheetName');

        return Excel::download($excel, 'Export file.xlsx');
    }



    private function list_id_visitors_have_tour()
    {
        $ids = TourVisitors::all()->unique('visitor_id')->pluck('visitor_id');
        return $ids;
    }
    private function list_all_routes_visitor($visitor_id, $filter_visitor, $filter_route, $filter_area)
    {
        $ids = TourVisitors::with(['Visitor', 'Route.area'])->where('visitor_id', $visitor_id);
        if ($filter_visitor) {
            $ids->whereHas('visitor', function ($query) use ($filter_visitor) {
                $query->where('first_name', 'like', '%' . $filter_visitor . '%')->orwhere('last_name', 'like', '%' . $filter_visitor . '%');
            });
        }
        if ($filter_route) {
            $ids->whereHas('route', function ($query) use ($filter_route) {
                $query->where('route', 'like', '%' . $filter_route . '%');
            });
        }
        if ($filter_area) {
            $ids->whereHas('route.area', function ($query) use ($filter_area) {
                $query->where('area', 'like', '%' . $filter_area . '%');
            });
        }
        $ids = $ids->get()->unique('route_id')->pluck('route_id');
        return $ids;
    }
    private function list_tour_by_visitor_and_route($visitor_id, $route_id, $filter_time = array())
    {
        $tours_visitor = TourVisitors::with('Visitor', 'Route', 'Route.area')->where('visitor_id', $visitor_id)->where('route_id', $route_id);

        if (isset($filter_time['daily'])) {
            $daily = Verta::parse($filter_time['daily']);
            $tours_visitor->whereDate('created_at', '=', $daily->DateTime()->format('Y-m-d'));
        }
        if (isset($filter_time['from_date']) && isset($filter_time['to_date'])) {
            $from_date = Verta::parse($filter_time['from_date']);
            $from_date = $from_date->DateTime()->format('Y-m-d H:i:s');
            $to_date = Verta::parse($filter_time['to_date']);
            $to_date = $to_date->DateTime()->format('Y-m-d H:i:s');
            $tours_visitor->whereBetween('created_at', [$from_date, $to_date]);
        } elseif (isset($filter_time['from_date'])) {
            $from_date = Verta::parse($filter_time['from_date']);
            $tours_visitor->whereDate('created_at', '>=', $from_date->DateTime()->format('Y-m-d'));
        } elseif (isset($filter_time['to_date'])) {
            $to_date = Verta::parse($filter_time['to_date']);
            $tours_visitor->whereDate('created_at', '<=', $to_date->DateTime()->format('Y-m-d'));
        }

        return $tours_visitor->get();
    }




    private function listCastomerInRoute($route_id)
    {
        return Users::with(['Routes'])->where('kind', 'customer')->whereHas('Routes', function ($q) use ($route_id) {
            $q->where('id', $route_id);
        })->get()->pluck('id');
    }

    private function  getFirstTimeVisit($user_ids, $Visitor_id, $date)
    {

        return VisitTime::select('time_start')
            ->whereIn('user_id', $user_ids)
            ->whereDate('created_at',  $date)
            ->where('visitor_id', $Visitor_id)
            ->orderBy('created_at', 'asc')
            ->first();
    }
    private function  getlastTimeVisit($user_ids, $Visitor_id, $date)
    {

        return  VisitTime::select('time_end')->whereIn('user_id', $user_ids)->where('visitor_id', $Visitor_id)
            ->whereDate('created_at',  $date)
            ->orderBy('created_at', 'DESC')->first();
    }
    private function  getTotalTimeRoute($user_ids, $Visitor_id)
    {

        return  VisitTime::select('time_end')->whereIn('user_id', $user_ids)->where('visitor_id', $Visitor_id)->orderBy('created_at', 'DESC')->first();
    }

    private function getTotalCustomerInRoute($route_id)
    {
        $all_user = array();
        $users = DB::select('select user_id from user_route where route_id = ?', [$route_id]);
        foreach ($users as $user) {
            array_push($all_user, $user->user_id);
        }
        return count($all_user);
    }


    /*  private function count_customer_is_visited($customer_id, $visitor_id, $route_id)
    {
        $start_Tor_visit = Carbon::now()->format('Y-m-d 00:00:00');
        $end_Tor_visit = Carbon::now()->format('Y-m-d 00:00:00');

        do {
            $preday = Carbon::createFromFormat('Y-m-d 00:00:00', $start_Tor_visit);
            $tourVisitorRoutes = TourVisitors::where('visitor_id', $visitor_id)
                ->where('route_id', $route_id)
                ->whereDate('date', '=',  $preday->subDay()->format('Y-m-d 00:00:00'))->count();
            if ($tourVisitorRoutes > 0) {
                $start_Tor_visit = $preday->subDay()->format('Y-m-d 00:00:00');
                continue;
            }
            break;
        } while (true);


        //dd(Carbon::now()->subDay()->format('Y-m-d 00:00:00'));
        $order = Order::where('visitor_id', $visitor_id)
            ->where('customer_id', $customer_id)
            ->where('registered_source', 'حضوری')
            ->whereBetween('created_at', [$start_Tor_visit, $end_Tor_visit])
            ->get();
        if ($order->count())
            return true;

        $reason_for_not_visitings = ReasonForNotVisiting::where('visitor_id', $visitor_id)
            ->where('customer_id', $customer_id)
            ->whereDate('created_at', '>=',$start_Tor_visit)
            ->whereDate('created_at', '<=',$end_Tor_visit)
           // ->whereBetween('created_at',  [$start_Tor_visit, $end_Tor_visit])
            ->get();
        if ($reason_for_not_visitings->count())
            return true;

        return false;
    }*/

    private function getTotalTimeTour($user_id, $visitor_id, $start_Tor_visit)
    {

        return $secound = VisitTime::select('time_visited')->whereIn('user_id', $user_id)->where('visitor_id', $visitor_id)
            ->whereDate('created_at',  $start_Tor_visit)
            ->get()->sum('time_visited');
        //return  gmdate("H:i:s", $secound);
    }
    private function getToTalCustomerisVisited($user_id, $visitor_id, $start_Tor_visit)
    {
        return VisitTime::select('user_id')->whereIN('user_id', $user_id)->where('visitor_id', $visitor_id)->whereDate('created_at',  $start_Tor_visit)->get()->unique('user_id')->count();
    }
    /*private function getToTalCustomerinDetailsTime($user_id, $visitor_id, $start_Tor_visit, $route_id)
    {
        /*  $datas = VisitTime::with([
            'Customer',
            'visitor'
        ])->whereIN('user_id', $user_id)->where('visitor_id', $visitor_id)->whereDate('created_at',  $start_Tor_visit)->get();

        foreach ($datas as $key => $data) {
            $order = Order::with('Details')
                ->where('visitor_id', $data->visitor_id)
                ->where('customer_id', $data->user_id)
                ->whereBetween('created_at', [$data->time_start, $data->time_end])
                ->first();

            if ($order) {
                $data->num_product = $order->Details->count();
                $data->order_id = $order->id;
                $data->final_price = $order->final_price;
            } else {
                $data->num_product = "*";
                $data->order_id = "*";
                $data->final_price = "*";
            }
            $datas[$key] = $data;
        }

        return $datas;

        $users = Users::with([
            'Routes', 'VisitTime' => function ($q) use ($start_Tor_visit) {
                return $q->whereDate('created_at',  $start_Tor_visit)->get();
            },
            /*   'Orders' => function ($q) use ($visitor_id, $start_Tor_visit) {
                return $q->where('visitor_id', $visitor_id)->where('registered_source', 'حضوری')->whereDate('created_at',  $start_Tor_visit);
            },
            'ReasonForNotVisitings' => function ($q) use ($visitor_id, $start_Tor_visit) {
                return $q->where('visitor_id', $visitor_id)->whereDate('created_at',  $start_Tor_visit);
            }
        ])
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($q) use ($route_id) {
                $q->where('id', $route_id);
            })
            ->get();


          /*foreach ($users as $key => $data) {
            $orders = Order::with('Details')
                //  ->where('visitor_id', $visitor_id)
                ->whereHas('Details')
                //   ->where('customer_id', $data->id)
                //    ->whereDate('created_at',  $start_Tor_visit)
                ->get();

    if($orders) continue;


                dd($orders->Details->sum('total'));

            if ($orders) {
                $data->num_product = $orders->Details->count();
                $data->order_id = $orders->id;
                $data->final_price = $orders->final_price;
            } else {
                $data->num_product = "*";
                $data->order_id = "*";
                $data->final_price = "*";
            }
            $datas[$key] = $data;
            dd($data->toArray());

        }
        return $users;
    }*/

    public function getToTalCustomerinDetailsTime(Request $request)
    {
        if (!$request->visitor_id) {
            throw new CoreException("ویزیتوری اجباریست");
        }
        if (!$request->start_time_visit) {
            throw new CoreException("start_time_visit اجباریست");
        }
        if (!$request->route_id) {
            throw new CoreException("route_id اجباریست");
        }

        $from_date = Verta::parse($request->start_time_visit);
        $from_date = $from_date->DateTime()->format('Y-m-d');

        $visitor_id = $request->visitor_id;
        $route_id = $request->route_id;
        $visitor_id = $request->visitor_id;
        $visi = Users::with('Visitor')->where('id', $visitor_id)->first();
        $name_visitor = $visi->first_name . " " . $visi->last_name;
        $users = Users::with([
            'Routes', 'VisitTime' => function ($q) use ($from_date) {
                return $q->whereDate('created_at',  $from_date)->get();
            }
        ])
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($q) use ($route_id) {
                $q->where('id', $route_id);
            })
            ->get();


        $datas = array();
        foreach ($users as $key => $data) {
            $orders = Order::with('Details')
                ->where('visitor_id', $visi->visitor->id)
                ->whereHas('Details')
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $from_date)
                ->get();

            $reson = ReasonForNotVisiting::where('visitor_id', $visitor_id)
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $from_date)->first();

            $num_product = 0;
            $order_id = "";
            $final_price = 0;
            foreach ($orders as $order) {
                $num_product += $order->details->sum('total');
                $final_price += $order->details->sum('final_price');
                $order_id .= $order->id . ",";
            }
            $data->visitor_name = $name_visitor;
            $data->num_product = $num_product;
            $data->order_id = $order_id;
            $data->order_id = $order_id;
            $data->status = $order_id;
            $count_visited = VisitTime::where('visitor_id', $visitor_id)->where('user_id', $data->id)->whereDate('created_at',  $from_date)->get()->count();
            if ($order_id != "") {
                $data->visit_status = "visit_success";
            } elseif ($reson) {
                $data->visit_status = "visit_not_success";
            } else {
                $data->visit_status = "not_visited";
            }
            $data->final_price = $final_price;
            $data->count_visited = $count_visited;
            $data->reson_for_not_visiting = (isset($reson->description)) ? $reson->description : "";
            $datas[$key] = $data;
        }

        return $users;
    }





    /*public function getToTalCustomerinDetailsTime(Request $request)
    {
        if (!$request->visitor_id) {
            throw new CoreException("ویزیتوری اجباریست");
        }
        if (!$request->start_time_visit) {
            throw new CoreException("start_time_visit اجباریست");
        }
        if (!$request->route_id) {
            throw new CoreException("route_id اجباریست");
        }

        $from_date = Verta::parse($request->start_time_visit);
        $from_date = $from_date->DateTime()->format('Y-m-d');

        $visitor_id = $request->visitor_id;
        $route_id = $request->route_id;
        $visi = Users::with('Visitor')->where('id', $visitor_id)->first();
        $name_visitor = $visi->first_name . " " . $visi->last_name;

       /*$visited= VisitTime::with(['Customer',
       'Orders' =>function($q){
        $q->whereDate('orders.created_at','>=','visit_time.time_start')->whereDate('orders.created_at','<=','visit_time.time_end');
       },
       'ReasonForNotVisiting' =>function($q){
        $q->whereDate('reason_for_not_visitings.created_at','>=','visit_time.time_start')->whereDate('reason_for_not_visitings.created_at','<=','visit_time.time_end');
       }])
       ->whereHas('Customer',function($q)use( $route_id){
           $q->whereHas('Routes',function($q1)use($route_id){
               $q1->where('id',$route_id);
           });
       })
       ->get();
       return $visited;

      $tour= TourVisitors::select('*')
      ->leftJoin('Routes','tour_visitors.route_id','=','routes.id')
      ->leftJoin('user_route','user_route.route_id','=','routes.id')
      ->leftJoin('users','user_route.user_id','=','users.id')
      ->leftJoin('visit_time','visit_time.user_id','=','users.id')
      ->whereDate('tour_visitors.date',$from_date)
      ->where('users.kind','customer')
      ->where('tour_visitors.visitor_id',$visitor_id)
      ->get();

      return $tour;
    }*/



    private function visited_is_success($customer_id, $visitor_id, $start_Tor_visit)
    {
        $visitor = Visi::where('user_id', $visitor_id)->first();
        if (!$visitor) return 0;
        $order = Order::where('visitor_id', $visitor->id)
            ->whereIn('customer_id', $customer_id)
            ->where('registered_source', 'حضوری')
            ->whereDate('created_at', $start_Tor_visit)
            ->get()->unique('customer_id');

        return $order->count();
    }



    private function paginate($items, $perPage = 1, $page = 2, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        //  $items = $items instanceof Collection ? $items : Collection::make($items);
        $row = array_slice($items, $perPage * ($page - 1), $perPage);
        return new LengthAwarePaginator($row, count($items), $perPage, $page, $options);
    }

    public  function get_list_route_visitor(VisitorRouteRequest $request)
    {
        $from = Verta::parse($request->from);
        $to = Verta::parse($request->to);
        $start_Tor_visit = $from->DateTime()->format('Y-m-d H:i:s');
        $end_Tor_visit = $to->DateTime()->format('Y-m-d H:i:s');
        $tour = TourVisitors::with(['Route', 'Visitor'])
            ->whereIn('visitor_id', $request->visitor_id)
            ->whereBetween('date', [$start_Tor_visit, $end_Tor_visit]);
        return $tour->get();
    }

    private function getClomenMergeHeader()
    {
        return [
            "N1:U1",
            "A1:A2",
            "B1:B2",
            "C1:C2",
            "D1:D2",
            "E1:E2",
            "F1:F2",
            "G1:G2",
            "H1:H2",
            "I1:I2",
            "J1:J2",
            "K1:K2",
            "L1:L2",
            "M1:M2"
        ];
    }


    private function getToTalCustomerinDetails($visitor_id, $start_time_visit, $route_id)
    {

        $results = array();
        $visi = Users::with('Visitor')->where('id', $visitor_id)->first();
        if ($visi == null) return array();
        $name_visitor = $visi->first_name . " " . $visi->last_name;
        $users = Users::with([
            'Routes', 'Addresses', 'VisitTime' => function ($q) use ($start_time_visit) {
                return $q->whereDate('created_at', $start_time_visit)->get();
            }
        ])
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($q) use ($route_id) {
                $q->where('id', $route_id);
            })
            ->get();


        $datas = array();
        foreach ($users as $key => $data) {
            $orders = Order::with('Details')
                ->where('visitor_id', $visi->visitor->id)
                ->whereHas('Details')
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $start_time_visit)
                ->get();

            $reson = ReasonForNotVisiting::where('visitor_id', $visitor_id)
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $start_time_visit)
                ->first();

            $num_product = 0;
            $order_id = "";
            $final_price = 0;
            foreach ($orders as $order) {
                $num_product += $order->details->sum('total');
                $final_price += $order->details->sum('final_price');
                $order_id .= $order->id . ",";
            }
            $status = "";
            $d = $data->toArray();


            if ($orders->count()) {
                $status = "ویزیت موفق";
            } elseif ($reson) {
                $status = "ویزیت ناموفق";
            } else {
                $status = "عدم ویزیت";
            }

            $results[] = [
                "full_name" => $d['first_name'] . " " . $d['last_name'],
                "customer_id" => $d['id'],
                "lat" => (isset($d['addresses'][0]['lat'])) ? $d['addresses'][0]['lat'] : "",
                "long" => (isset($d['addresses'][0]['long'])) ? $d['addresses'][0]['long'] : "",
                "status" => $status,
                "visitor_name" => $name_visitor,
                "visit_time" => $d['visit_time'],
                "num_product" => $num_product,
                "order_id" => $order_id,
                "final_price" => $final_price,
                "reson_for_not_visiting" => (isset($reson->description)) ? $reson->description : "",
            ];
        }

        return $results;
    }
    /*private function getToTalCustomerinDetailsdablicate($visitor_id, $start_time_visit, $route_id)
    {
        $results = array();
        $visi = Users::with('Visitor')->where('id', $visitor_id)->first();
        if ($visi == null) return array();
        $name_visitor = $visi->first_name . " " . $visi->last_name;


       $users = Users::with([
            'Routes', 'Addresses', 'VisitTime' => function ($q) use ($start_time_visit) {
                return $q->whereDate('created_at', $start_time_visit)->get();
            }
        ])
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($q) use ($route_id) {
                $q->where('id', $route_id);
            })
            ->get();


        $datas = array();
        foreach ($users as $key => $data) {
            $orders = Order::with('Details')
                ->where('visitor_id', $visi->visitor->id)
                ->whereHas('Details')
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $start_time_visit)
                ->get();

            $reson = ReasonForNotVisiting::where('visitor_id', $visitor_id)
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $start_time_visit)
                ->first();

            $num_product = 0;
            $order_id = "";
            $final_price = 0;
            foreach ($orders as $order) {
                $num_product += $order->details->sum('total');
                $final_price += $order->details->sum('final_price');
                $order_id .= $order->id . ",";
            }
            $status = "";
            $d = $data->toArray();


            if ($orders->count()) {
                $status = "ویزیت موفق";
            } elseif ($reson) {
                $status = "ویزیت ناموفق";
            } else {
                $status = "عدم ویزیت";
            }

            $results[] = [
                "full_name" => $d['first_name'] . " " . $d['last_name'],
                "customer_id" => $d['id'],
                "lat" => (isset($d['addresses'][0]['lat'])) ? $d['addresses'][0]['lat'] : "",
                "long" => (isset($d['addresses'][0]['long'])) ? $d['addresses'][0]['long'] : "",
                "status" => $status,
                "visitor_name" => $name_visitor,
                "visit_time" => $d['visit_time'],
                "num_product" => $num_product,
                "order_id" => $order_id,
                "final_price" => $final_price,
                "reson_for_not_visiting" => (isset($reson->description)) ? $reson->description : "",
            ];
        }

        return $results;
    }*/


    private function getToTalCustomerinDetailsdablicate($visitor_id, $start_time_visit, $route_id)
    {
        $results = array();
        $visi = Users::with('Visitor')->where('id', $visitor_id)->first();
        if ($visi == null) return array();
        $name_visitor = $visi->first_name . " " . $visi->last_name;


        $users = Users::with([
            'Routes', 'Addresses', 'VisitTime' => function ($q) use ($start_time_visit) {
                return $q->whereDate('created_at', $start_time_visit)->get();
            }
        ])
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($q) use ($route_id) {
                $q->where('id', $route_id);
            })
            ->get();


        $datas = array();
        foreach ($users as $key => $data) {
            $orders = Order::with('Details')
                ->where('visitor_id', $visi->visitor->id)
                ->whereHas('Details')
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $start_time_visit)
                ->get();

            $reson = ReasonForNotVisiting::where('visitor_id', $visitor_id)
                ->where('customer_id', $data->id)
                ->whereDate('created_at',  $start_time_visit)
                ->first();

            $num_product = 0;
            $order_id = "";
            $final_price = 0;
            $d = $data->toArray();

            foreach ($orders as $order) {
                $results[] = [
                    "full_name" => $d['first_name'] . " " . $d['last_name'],
                    "customer_id" => $d['id'],
                    "lat" => (isset($d['addresses'][0]['lat'])) ? $d['addresses'][0]['lat'] : "",
                    "long" => (isset($d['addresses'][0]['long'])) ? $d['addresses'][0]['long'] : "",
                    "status" => "ویزیت موفق",
                    "visitor_name" => $name_visitor,
                    "visit_time" => $d['visit_time'],
                    "num_product" => $order->details->sum('total'),
                    "order_id" => $order->id,
                    "final_price" => $order->details->sum('final_price'),
                    "reson_for_not_visiting" =>  "",
                ];
            }

            if ($reson) {
                $results[] = [
                    "full_name" => $d['first_name'] . " " . $d['last_name'],
                    "customer_id" => $d['id'],
                    "lat" => (isset($d['addresses'][0]['lat'])) ? $d['addresses'][0]['lat'] : "",
                    "long" => (isset($d['addresses'][0]['long'])) ? $d['addresses'][0]['long'] : "",
                    "status" => "ویزیت ناموفق",
                    "visitor_name" => $name_visitor,
                    "visit_time" => $d['visit_time'],
                    "num_product" => "0",
                    "order_id" => "0",
                    "final_price" => "0",
                    "reson_for_not_visiting" => (isset($reson->description)) ? $reson->description : "",
                ];
            }



            if (!($orders->count() || $reson)) {


                $results[] = [
                    "full_name" => $d['first_name'] . " " . $d['last_name'],
                    "customer_id" => $d['id'],
                    "lat" => (isset($d['addresses'][0]['lat'])) ? $d['addresses'][0]['lat'] : "",
                    "long" => (isset($d['addresses'][0]['long'])) ? $d['addresses'][0]['long'] : "",
                    "status" => "عدم ویزیت",
                    "visitor_name" => $name_visitor,
                    "visit_time" => $d['visit_time'],
                    "num_product" => $num_product,
                    "order_id" => $order_id,
                    "final_price" => $final_price,
                    "reson_for_not_visiting" => (isset($reson->description)) ? $reson->description : "",
                ];
            }
        }

        return $results;
    }



    private function converDateMToJ($date)
    {

        $date_create = new Verta1($date);
        return str_replace('-', '/', $date_create->formatDate('H:i:s'));
    }
    private function getTime($date)
    {

        $time = strtotime($date);
        return $newformat = date('H:i:s', $time);
    }
    private function converDateJToM($date)
    {
        try {
            $to_date = Verta::parse($date);
            return $to_date->DateTime()->format('Y-m-d');
        } catch (Throwable $e) {
            return "";
        }
    }
}
