<?php

namespace App\Http\Controllers\api\Visitor\v1\route;

use App\Models\User\User;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Core\Packages\visitor\Visitors;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User\ReasonForNotVisiting;

use Core\Packages\tour_visit\TourVisitors;
use App\Http\Requests\api\Customer\v1\Billing\Invoice\StoreInvoiceRequest;

class RouteController extends Controller
{
    public function index(Request $request)
    {

        $filter_customer = null;
        if (isset($request->name_customer))
            $filter_customer = $request->name_customer;
        $filter_store = null;
        if (isset($request->filter_store))
            $filter_store = $request->filter_store;

        //$visitor_id = auth('mobile')->user()->id; #error
        $visitor_id = auth('mobile')->user()['id'];
        $id = $request->id;
        $name = $request->name;

        $visitor = Visitors::with([
            'routes' => function ($query) use ($request) {
                if ($request->route_id) {
                    $query->where('id', $request->route_id);
                }
            }, 'routes.customers' => function ($query) use ($filter_customer, $filter_store, $id) {
                if ($id) {
                    $query->where('id', $id);
                }
                if ($filter_customer) {
                    $keywords = explode(' ', $filter_customer);
                    foreach ($keywords as $keyword) {
                        $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%');
                    }
                }
                if ($filter_store) {
                    $keywords = explode(' ', $filter_store);
                    foreach ($keywords as $keyword) {
                        $query->where('store_name', 'like', '%' . $keyword . '%');
                    }
                }
            }, 'routes.customers' => function ($query) use ($name, $id) {
                $query->where('status', 'active');
            },  'routes.customers.Addresses'
        ])->where('is_super_visitor', 0)->whereHas('user', function ($query) use ($id, $visitor_id) {
            $query->where('id', $visitor_id);
        })->first();



        if (!$visitor) {
            return [
                'status' => false,
                'message' => "ویزیتور یافت نشد",
            ];
        }

        if (count($visitor->routes) > 0 and $visitor->routes[0]) {
            $this->getCustomersAccessToken($visitor->routes);

            $routes = $visitor->routes->pluck('id')->all();

            $tourVisitorRoutes = TourVisitors::where('visitor_id', $visitor_id)
                ->whereIn('route_id', $routes)
                ->whereDate('date', '=', Carbon::now()->format('Y-m-d 00:00:00'))
                ->pluck('route_id')->all();

            $resultt = [];
            foreach ($visitor->routes as $route) {
                if (in_array($route->id, $tourVisitorRoutes)) {
                    $resultt[] = Routes::with(['Customers', 'Customers.Addresses'])->where('id', $route->id)->get()->toArray();
                }
            }



            $result = array();
            foreach ($resultt as  $re) {
                array_push($result, $re[0]);
            }

            foreach ($result as $keyd => $data) {

                foreach ($data['customers'] as $keyc => $customer) {
                    $isVisited = $this->customer_is_visited($customer['id'], $visitor_id, $data['id']);
                    $token = $this->getCustomersAccessTokenUser($customer['id']);
                    $result[$keyd]['customers'][$keyc]["visited"] = $isVisited;
                    $result[$keyd]['customers'][$keyc]["access_token"] = (isset($token->result['access_token'])) ? $token->result['access_token'] : null;
                }
            }

            return $result;
        } else {
            return [
                'status' => false,
                'message' => "مسیری برای ویزتور ارسالی یافت نشد",
            ];
        }
    }

    public function customer_list(Request $request)
    {

        // $visitor_id = auth('mobile')->user()->id; error
        $filter_customer = null;
        if (isset($request->name_customer))
            $filter_customer = $request->name_customer;
        $filter_store = null;
        if (isset($request->filter_store))
            $filter_store = $request->filter_store;
        $filter_refral = null;
        if (isset($request->filter_refral))
            $filter_refral = $request->filter_refral;
        $filter_mobile = null;
        if (isset($request->filter_mobile))
            $filter_mobile = $request->filter_mobile;
        $filter_phone = null;
        if (isset($request->filter_phone))
            $filter_phone = $request->filter_phone;
        $filter_address = null;
        if (isset($request->filter_address))
            $filter_address = $request->filter_address;

        $visitor_id = auth('mobile')->user()['id'];
        $id = $request->id;
        $visitor = Visitors::with([
            'routes' => function ($query) use ($request) {
                if ($request->route_id) {
                    $query->where('id', $request->route_id);
                }
            }, 'routes.customers' => function ($query) use ($filter_customer, $filter_store, $id, $filter_refral, $filter_mobile, $filter_phone, $filter_address) {
                if ($id) {
                    $query->where('id', $id);
                }

                if (!$filter_refral==null) {
                    $query->where('id', 'like',$filter_refral.'%');
                }
                if ($filter_mobile) {
                    $query->where('mobile_number', 'like',  '%'.$filter_mobile . '%');
                }
                if ($filter_phone) {
                    $query->where('phone_number', 'like',  '%'.$filter_phone . '%');
                }

                if ($filter_customer) {
                    $keywords = explode(' ', $filter_customer);
                    foreach ($keywords as $keyword) {
                        $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%');
                    }
                }
                if ($filter_address) {
                    $keywords = explode(' ', $filter_address);
                    $query->whereHas('Addresses', function ($q1) use ($keywords) {
                        foreach ($keywords as $keyword) {
                            $q1->where('address', 'like',  '%' . $keyword . '%');
                        }
                    });
                }
                if ($filter_store) {
                    $keywords = explode(' ', $filter_store);
                    foreach ($keywords as $keyword) {
                        $query->where('store_name', 'like',  '%' . $keyword . '%');
                    }
                }
            },  'routes.customers.Addresses'
        ])->where('is_super_visitor', 0)->whereHas('user', function ($query) use ($id, $visitor_id) {
            $query->where('id', $visitor_id);
        })->first();


        if (!$visitor) {
            return [
                'status' => false,
                'message' => "مشتری یافت نشد",
            ];
        }



        if (count($visitor->routes)) {
            $this->getCustomersAccessToken($visitor->routes);
            $result = [];
            foreach ($visitor->routes as $route) {
                if (count($route->customers)) {
                    foreach ($route->customers as $customer) {
                        $customer['visited'] = $this->customer_is_visited($customer['id'], $visitor_id, $route['id']);
                        $result[] = $customer;
                    }
                }
            }
            return $result;

            return $visitor->routes;
        } else {
            return [
                'status' => false,
                'message' => "مسیری برای ویزتور ارسالی یافت نشد",
            ];
        }
    }


    public function getCustomersAccessToken($routes)
    {
        $result = [];
        foreach ($routes as $route) {
            if (count($route->customers)) {
                foreach ($route->customers as $key => $customer) {
                    $user = Auth::loginUsingId($customer['id']);
                    if (!empty($user)) {
                        $token = JWTAuth::fromUser($user);
                        if (!empty($token)) {
                            $result = Users::_()->createNewToken($token, $user);
                            if (!empty($result)) {
                                $route->customers[$key]['access_token'] =
                                    $result->result['access_token'];
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    private function customer_is_visited($customer_id, $visitor_id, $route_id)
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
            ->whereDate('created_at', '>=', $start_Tor_visit)
            ->whereDate('created_at', '<=', $end_Tor_visit)
            // ->whereBetween('created_at',  [$start_Tor_visit, $end_Tor_visit])
            ->get();
        if ($reason_for_not_visitings->count())
            return true;

        return false;
    }
    public function CustomerRegisterByVisitor(Request $request)
    {

        $customer = User::where('users.kind', User::KIND_CUSTOMER)
            ->select('users.*')
            ->with([
                'provinces',
                'cities',
                'Areas',
                'routes',
                "PriceClasses",
                'addresses',
                'IntroducerCode',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
            ])
            ->where("introducer_code_id", auth('mobile')->user()['id'])
            ->get();
        return $customer;
    }


    public function getCustomersAccessTokenUser($user_id)
    {
        $d = [];
        $user = Auth::loginUsingId($user_id);
        if (!empty($user)) {
            $token = JWTAuth::fromUser($user);
            if (!empty($token)) {
                $result = Users::_()->createNewToken($token, $user);
                if (!empty($result)) {

                    $result->result['access_token'];
                }
            }
        }
        return $result;
    }
}
