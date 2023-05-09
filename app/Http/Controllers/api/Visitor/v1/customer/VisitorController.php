<?php

namespace App\Http\Controllers\api\Visitor\v1\customer;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use Core\Packages\order\Visi;
use App\Models\User\VisitTime;
use App\Models\User\NotVisited;
use Core\Packages\visitor\Visitors;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Customer\v1\Billing\Invoice\StoreInvoiceRequest;

class VisitorController extends Controller
{
    public function customers(Request $request)
    {
        $visitor_id = $request->visitor_id;
        $id = $request->id;
        $name = $request->name;
        $visitor = Visitors::with('routes')->where('is_super_visitor', 0)->find($visitor_id);
        if (!$visitor) {
            return [
                'status'       => false,
                'message' => "ویزیتور یافت نشد",
            ];
        }

        if (count($visitor->routes) > 0 and $visitor->routes[0]) {
            if (isset($visitor->routes[0]->id)) {

                $routes_id = $visitor->routes[0]->id;

                $cities = auth('api')->user()->Cities->pluck('id')->all();
                $customer = User::where('users.kind', User::KIND_CUSTOMER)
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

                $customer =  $customer->get();
                if (count($customer) < 1) {
                    return [
                        'status'       => false,
                        'message' => " مشتری برای این ویزیتور یافت نشد",
                    ];
                }
                return $customer;
            }
        } else {
            return [
                'status'       => false,
                'message' => "مسیری برای ویزتور ارسالی یافت نشد",
            ];
        }
    }



    public function CustomerRegisterByVisitor(Request $request)
    {
        //auth()->id()

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
            ])->where("introducer_code_id", auth()->id())->get();
        return $customer;
    }


    public function setTimeVisit(Request $request)
    {
        $now_time = time();
        if (isset($request->user_id)) {
            $visit_time = new  VisitTime();
            $visit_time->user_id = $request->user_id;
            $visit_time->long = $request->long;
            $visit_time->lat = $request->lat;
            $visit_time->visitor_id = auth('api')->user()->id;
            $visit_time->time_start = date('Y-m-d H:i:s', $request->time_visit);
            $visit_time->time_end =  date('Y-m-d H:i:s', $now_time);
            $visit_time->time_visited = ($now_time - $request->time_visit);
            $visit_time->save();
            return [
                'status'       => true,
                'message' => "ثبت شد",
            ];
        }
        return [
            'status'       => false,
            'message' => "ثبت نشد",
        ];
    }
    public function getTime()
    {
        return time();
    }




    public function ListNotVisited(Request $request)
    {
        $not_visited = NotVisited::where('company_id', auth('api')->user()->company_id);

        return $not_visited->get();
    }

    public function getOrderRegisterByVisitor(Request $request)
    {
        $visitor_ = auth('mobile')->user()['id'];
        $visitor = Visi::where('user_id', $visitor_)->first();
        $visitor_id = $visitor->id;
        $orders_by_visitr = Order::with(['Customer', 'Details', 'Company'])
            ->whereHas('Details')
            ->where('visitor_id', $visitor_id);
        if ($request->filter_customer) {
            $orders_by_visitr->whereIn('customer_id', $request->filter_customer);
        }
        if ($request->registered_source) {
            // $orders_by_visitr->where('registered_source', $request->registered_source);
            //  $orders_by_visitr->where('registered_source', 'ویزیت تلفنی');
            $orders_by_visitr->where('registered_source', $request->registered_source)->orwhere('registered_source', $request->registered_source);
        }

        return   $orders_by_visitr->orderBy('created_at', 'desc')->take(50)->get();
    }
    public function VisitorIsHaveOrderForUser(Request $request)
    {
        $visitor_ = auth('mobile')->user()['id'];
        $visitor = Visi::where('user_id', $visitor_)->first();
        $visitor_id = $visitor->id;
        $orders_by_visitr = Order::with(['Customer', 'Details', 'Company'])
            ->whereHas('Details')
            ->where('visitor_id', $visitor_id);
        if ($request->filter_customer) {
            $orders_by_visitr->whereIn('customer_id', $request->filter_customer);
        }
        return $orders_by_visitr->where('registered_source', 'حضوری')->whereDate('created_at',date("Y-m-d"))->get()->count();
    }
}
