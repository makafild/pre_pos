<?php

namespace App\Http\Controllers\api\Visitor\v1\position;

use App\Http\Requests\Visitor\StorePositionRequest;

//use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User\VisitorPosition;
use File;
use Response;
use DB;

class PositionController extends Controller
{
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

    public function index()
    {
        // if (!empty(request()->get('from_date')) and !empty(request()->get('to_date'))) {
        //  $from = date('Y-m-d H:i:s',request()->get('from_date'));
        // $to = date('Y-m-d H:i:s',request()->get('to_date'));

        // $visitorPositions = VisitorPosition::
        //  whereBetween('created_at', [$from, $to])->paginate();
        //  } else {


        if (request()->has('geo') && request()->get('geo') == 'true') {
            ini_set('memory_limit', '512M');
            $jsongFile = time() . '.geojson';

            if (request()->get('visitors') && is_array(request()->get('visitors')) && request()->get('from_date') && request()->get('to_date')) {
                $visitorPositions = VisitorPosition::whereIn('user_id', request()->get('visitors'))
                    ->whereBetween(DB::raw('date(created_at)'), array(request()->get('from_date'), request()->get('to_date')))
                    ->get();
            }else{
                $visitorPositions = VisitorPosition::get();
            }


            File::put(public_path('/upload/json/' . $jsongFile), $this->geoJson($visitorPositions));
            return [
                'status' => true,
                'url' => url('/') . '/upload/json/' . $jsongFile
            ];
        } else {
            if (request()->get('visitors') && is_array(request()->get('visitors')) && request()->get('from_date') && request()->get('to_date')) {
                $visitorPositions = VisitorPosition::whereIn('user_id', request()->get('visitors'))
                    ->whereBetween(DB::raw('date(created_at)'), array(request()->get('from_date'), request()->get('to_date')))
                    ->paginate();
            }else{
                $visitorPositions = VisitorPosition::paginate();
            }

        }

        // }
        return $visitorPositions;
    }

    public function show($id)
    {
        $visitorPositions = VisitorPosition::find($id);
        return $visitorPositions;
    }

    public function store(StorePositionRequest $request)
    {
        $data = [];
        foreach ($request['positions'] as $permission) {
            $data[] = [
                'user_id' => auth('api')->user()['id'],
                'accessibility' => $permission['accessibility'],
                'device_id' => $permission['device_id'],
                'accuracy' => isset($permission['accuracy']) ? $permission['accuracy'] : null,
                'altitude' => isset($permission['altitude']) ? $permission['altitude'] : null,
                'heading' => isset($permission['heading']) ? $permission['heading'] : null,
                'latitude' => isset($permission['latitude']) ? $permission['latitude'] : null,
                'longitude' => isset($permission['longitude']) ? $permission['longitude'] : null,
                'speed' => isset($permission['speed']) ? $permission['speed'] : null,
                'timestamp' => isset($permission['timestamp']) ? $permission['timestamp'] : null,
                'timeout' => isset($permission['timeout']) ? $permission['timeout'] : null,
                'position_unavailable' => isset($permission['position_unavailable']) ? $permission['position_unavailable'] : null,
                'permission_denied' => isset($permission['permission_denied']) ? $permission['permission_denied'] : null,
                'message' => isset($permission['message']) ? $permission['message'] : null,
                'code' => isset($permission['code']) ? $permission['code'] : null,
                'location_status' => $permission['location_status'],
                'network' => $permission['network'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        VisitorPosition::insert($data);

        return [
            'status' => true,
            'message' => trans('messages.visitor.position.store')
        ];
    }
}
