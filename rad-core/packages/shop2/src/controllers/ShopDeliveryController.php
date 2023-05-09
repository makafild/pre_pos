<?php

namespace core\Packages\shop\src\controllers;

use phpseclib\Net\SSH1;
use Illuminate\Http\Request;
use Hamcrest\Core\HasToString;
use Core\Packages\gis\UserRoute;
use Hekmatinasser\Verta\Facades\Verta;

use Core\Packages\gis\DeliveryRouteUser;
use Core\System\Exceptions\CoreException;
use Core\Packages\shop\ShopDeliveryTime;
use Core\Packages\shop\ShopTourDelivery;
use Core\Packages\shop\ShopDeliveryDates;
use Core\System\Http\Controllers\BaseController;
use Core\System\Http\Controllers\CoreController;

class ShopDeliveryController extends BaseController
{


    private $_store_fillable = [
        'tour_visits',
        'date_id'
    ];

    private $_update_fillable = [
        'visitor_id',
        'route_id',
        'dates'
    ];

    public function index(Request $request, $id, $limit = 10)
    {


        $del = ShopTourDelivery::with('dates.times')->orderBy('created_at', 'DESC');
        if (auth('api')->user()['kind'] == "company")
            $del->where('company_id', auth('api')->user()['company_id']);



        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $del = $del->get();
        } else {
            $del = $del->jsonPaginate($limit);
        }

        return $del;
    }

    public function list(Request $request)
    {


        $result = ShopTourDelivery::_()->list($request);


        return $this->responseHandler2($result);
    }

    public function show($id)
    {
        return $result = ShopTourDelivery::with('dates.times')->find($id);
    }


    public function update(Request $request, $id)
    {

        $tour = ShopTourDelivery::where("id", $id)->first();
        if (!$tour) throw new CoreException('یافت نشد');
        $tour->route_id = $request->route_id;
        $tour->save();
        //delete
        $date = ShopDeliveryDates::where('delivery_tour_id', $tour->id)->get()->pluck('id')->toArray();
        if (count($date))
            ShopDeliveryTime::whereIn('date_times_id', $date)->delete();
        ShopDeliveryDates::where('id', $tour->id)->delete();
        //--------

        foreach ($request->dates as $date) {
            $date_tour = new ShopDeliveryDates();
            $date_tour->delivery_tour_id = $tour->id;
            $date_tour->date_times = $date['date'];
            $date_tour->save();
            foreach ($date['time'] as $time) {
                $dt = new ShopDeliveryTime();
                $dt->date_times_id = $date_tour->id;
                $dt->start = $time['time_start'];
                $dt->end = $time['time_end'];
                $dt->save();
            }
        }

        return [
            'status' => true,
            'message' => "با موفقیت ثبت شد",

        ];
    }


    public function store(Request $request)
    {

        // $tour = ShopTourDelivery::where("route_id", $request->route_id)->get();
        // dd($tour);

        // if (!$tour) throw new CoreException('یافت نشد');



        // foreach ($tour as $t) {
        //     $date = ShopDeliveryDates::where('delivery_tour_id', $t->id)->get()->pluck('id');

        //     if (count($date)) {
        //         ShopDeliveryTime::where('date_times_id', $date)->delete();

        //         ShopDeliveryDates::where('delivery_tour_id', $t->id)->delete();
        //     }
        // }
        // $tour = ShopTourDelivery::where("route_id", $request->route_id)->delete();




        $tour = new ShopTourDelivery();
        // $tour->route_id = $request->route_id;

        if (auth('api')->user()->kind == 'vendor'){


             $tour->vendor_id = auth('api')->user()['company_id'];
            $tour->city_id = $request['city_id'];
        }


        else
        $tour->vendor_id = $request->company_id;
        $tour->save();



        foreach ($request->dates as $date) {
            $date_tour = new ShopDeliveryDates();
            $date_tour->tour_id = $tour->id;
            $date_tour->date = $date['date'];
            $date_tour->save();
            foreach ($date['time'] as $time) {
                $dt = new ShopDeliveryTime();
                $dt->date_id = $date_tour->id;
                $dt->start = $time['time_start'];
                $dt->end = $time['time_end'];
                $dt->save();
            }
        }



        return [
            'status' => true,
            'message' => "با موفقیت ثبت شد",

        ];
    }







    public function delete(Request $request)
    {



        $tours = ShopTourDelivery::whereIn('id', $request->ids)->get();
        foreach ($tours as $tour) {
            $date = ShopDeliveryDates::where('delivery_tour_id', $tour->id)->get()->pluck('id')->toArray();
            if (count($date))
                ShopDeliveryTime::whereIn('date_times_id', $date)->delete();

            ShopDeliveryDates::where('id', $tour->id)->delete();
            ShopTourDelivery::where('id',  $tour->id)->delete();
        }

        return [
            'status' => true,
            'message' => "با موفقیت حذف شد",
            'id' => "",
        ];
    }




}
