<?php

namespace core\Packages\tour_delivery\src\controllers;

use phpseclib\Net\SSH1;
use Illuminate\Http\Request;
use Hamcrest\Core\HasToString;
use Core\Packages\gis\UserRoute;
use Hekmatinasser\Verta\Facades\Verta;

use Core\Packages\gis\DeliveryRouteUser;
use Core\System\Exceptions\CoreException;
use Core\Packages\tour_delivery\DeliveryTime;
use Core\Packages\tour_delivery\TourDelivery;
use Core\Packages\tour_delivery\DeliveryDates;
use Core\System\Http\Controllers\BaseController;
use Core\System\Http\Controllers\CoreController;

class TourDeliveryController extends BaseController
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
        $del = TourDelivery::with('dates.times')->orderBy('created_at', 'DESC');
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


        $result = TourDelivery::_()->list($request);


        return $this->responseHandler2($result);
    }

    public function show($id)
    {
        return $result = TourDelivery::with('dates.times')->find($id);
    }


    public function update(Request $request, $id)
    {

        $tour = TourDelivery::where("id", $id)->first();
        if(!$tour) throw new CoreException('یافت نشد');
        $tour->route_id = $request->route_id;
        $tour->save();
        //delete
        $date = DeliveryDates::where('delivery_tour_id', $tour->id)->get()->pluck('id')->toArray();
        if (count($date))
            DeliveryTime::whereIn('date_times_id', $date)->delete();
        DeliveryDates::where('id', $tour->id)->delete();
        //--------

        foreach ($request->dates as $date) {
            $date_tour = new DeliveryDates();
            $date_tour->delivery_tour_id = $tour->id;
            $date_tour->date_times = $date['date'];
            $date_tour->save();
            foreach ($date['time'] as $time) {
                $dt = new DeliveryTime();
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

        $tour = TourDelivery::where("route_id", $request->route_id)->get();

        if (!$tour) throw new CoreException('یافت نشد');



        foreach ($tour as $t) {
            $date = DeliveryDates::where('delivery_tour_id', $t->id)->get()->pluck('id');

            if (count($date)) {
                DeliveryTime::where('date_times_id', $date)->delete();

                DeliveryDates::where('delivery_tour_id', $t->id)->delete();
            }
        }
        $tour = TourDelivery::where("route_id", $request->route_id)->delete();

     


        $tour = new TourDelivery();
        $tour->route_id = $request->route_id;
        if (auth('api')->user()['company_id'])
            $tour->company_id = auth('api')->user()['company_id'];
        else
            $tour->company_id = $request->company_id;

        $tour->save();



        foreach ($request->dates as $date) {
            $date_tour = new DeliveryDates();
            $date_tour->delivery_tour_id = $tour->id;
            $date_tour->date_times = $date['date'];
            $date_tour->save();
            foreach ($date['time'] as $time) {
                $dt = new DeliveryTime();
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







    public function delete(Request $request)
    {



        $tours = TourDelivery::whereIn('id', $request->ids)->get();
        foreach ($tours as $tour) {
            $date = DeliveryDates::where('delivery_tour_id', $tour->id)->get()->pluck('id')->toArray();
            if (count($date))
                DeliveryTime::whereIn('date_times_id', $date)->delete();

            DeliveryDates::where('id', $tour->id)->delete();
            TourDelivery::where('id',  $tour->id)->delete();
        }

        return [
            'status' => true,
            'message' => "با موفقیت حذف شد",
            'id' => "",
        ];
    }
}
