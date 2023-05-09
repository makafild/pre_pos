<?php

namespace core\Packages\visitor_position\src\controllers;

use File;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Core\Packages\user\Users;
use Core\Packages\order\Order;
use Illuminate\Support\Facades\DB;
use Hekmatinasser\Verta\Facades\Verta;
use Hekmatinasser\Verta\Verta as vertai;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\visitor_position\VisitorPosition;
use Core\Packages\visitor_position\src\request\VisitorPositionRequest;


class VisitorPositionController extends CoreController
{

    public function list()
    {
        $result = Users::whereHas('visitorPosition')->with(['visitorPosition'])->latest()->jsonPaginate();
        return $result;
    }



    public function VisitorPositions(Request  $request)
    {
        $from = Verta::parse($request->date);
        $date = $from->DateTime()->format('Y-m-d');
        $result = [
            "locations" => array(),
            "start_location" => array(),
            "end_location" => array(),
            "customers_location" => '',
        ];
        $visitor = Users::find($request->user_id);
        $name = "";
        if (isset($visitor->first_name))
            $name .= $visitor->first_name;
        if (isset($visitor->last_name))
            $name .= " " . $visitor->last_name;
        $lists = VisitorPosition::select('latitude', 'longitude', 'created_at')
            ->where('user_id', $request->user_id)
            ->where('latitude', '!=', NULL)
            ->where('longitude', '!=', NULL)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get();


        foreach ($lists as $key => $list) {
            $temp = array();
            $date_create = new vertai($list['created_at']);

            $temp['loc'] = [$list['longitude'], $list['latitude']];
            $temp['attributes']['time'] = $date_create->DateTime()->format('H:i:s');
            $result['locations'][] = $temp;

            if ($key == 0) {
                $temp['attributes']['start_date'] = str_replace('-', '/', $date_create->formatDate('H:i:s'));
                $temp['attributes']['visitor'] = $name;
                $result['start_location'] = $temp;
            }

            if ($lists->count() - 1 == $key) {
                $temp['attributes']['end_date'] = str_replace('-', '/', $date_create->formatDate('H:i:s'));
                $temp['attributes']['visitor'] = $name;
                $result['end_location'] = $temp;
            }
        }
        $result['customers_location'] = $this->create_file_jeojson($request->route_id);

        return $result;
    }

    private function create_file_jeojson($route_id)
    {
        $customer =  Users::with('Routes')
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($q) use ($route_id) {
                $q->where('id', $route_id);
            })->get();

        $customerIds = $customer->pluck('id');
        $orders = Order::select('customer_id', 'created_at')
            ->join(DB::raw('(Select max(id) as id from orders group by customer_id) LatestMessage'), function ($join) {
                $join->on('orders.id', '=', 'LatestMessage.id');
            })->whereIN('customer_id', $customerIds)->get()->toArray();

        $customerOrders = [];
        foreach ($orders as $order) {
            $customerOrders[$order['customer_id']] = $order['created_at'];
        }


        foreach ($customer as $index => $cu) {
            if (isset($customerOrders[$cu['id']])) {
                $v = new Vertai($customerOrders[$cu['id']]);
                $customer[$index]['order_last_date'] = str_replace('-', '/', $v->formatDate());
            }

            if (!empty($cu['categories'][0]['constant_fa'])) {
                $customer[$index]['category_name'] = $cu['categories'][0]['constant_fa'];
            }

            $customer[$index]['address'] = (isset($cu['addresses'][0]['address'])) ? $cu['addresses'][0]['address'] : "null";
        }

        $jsongFile = time() . '.geojson';
       // dd( base_path().'/public_html/upload/json/');
        File::put(base_path().'/public_html/upload/json/'. $jsongFile, $this->geoJson($customer));
        return  url('/') . '/upload/json/' . $jsongFile;
    }


    function geoJson($customers)
    {
        $original_data = json_decode($customers, true);
        $features = [];
        $lat = 0;
        $long = 0;
        foreach ($original_data as $value) {
            if (!empty($value['addresses'])) {
                foreach ($value['addresses'] as $address) {
                    $lat = $address['lat'];
                    $long = $address['long'];
                }
            }
            $features[] = [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$long, $lat]],
                'properties' => $value,
            ];
        };
        $allfeatures = ['type' => 'FeatureCollection', 'features' => $features];
        return json_encode($allfeatures, JSON_PRETTY_PRINT);
    }
}
