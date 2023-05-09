<?php

namespace core\Packages\tour_visit\src\controllers;

use Core\Packages\tour_visit\TourVisitors;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\tour_visit\src\request\TourVisitorsStoreRequest;
use Core\Packages\tour_visit\src\request\TourVisitorsUpdateRequest;
use Illuminate\Http\Request;

class TourVisitController extends CoreController
{

    private $_store_fillable = [
        'tour_visits'
    ];

    private $_update_fillable = [
        'visitor_id',
        'route_id',
        'dates'
    ];

    public function list()
    {
        $result = TourVisitors::_()->list();
        return $this->responseHandler2($result);
    }

    public function show($visitorId)
    {
        $result = TourVisitors::_()->show($visitorId);
        return $this->responseHandler2($result);
    }

    public function store(TourVisitorsStoreRequest $request)
    {
        $payload = $request->only($this->_store_fillable);
        $result = TourVisitors::_()->store($payload);
        return $this->responseHandler2($result);
    }

    public function update(TourVisitorsUpdateRequest $request)
    {
        /* $payload = $request->only($this->_update_fillable);
        $result = TourVisitors::_()->updateR($payload);
        return $this->responseHandler2($result);*/
        $payload = $request->only($this->_store_fillable);
        $inputVisitors = [];
        foreach ($payload['tour_visits'] as $tourVisit) {
            $inputVisitors[] = $tourVisit['visitor_id'];
            $inputRoutes = [];
            foreach ($tourVisit['routes'] as $visitor) {
                $inputRoutes[] = $visitor['id'];
                //TourVisitors::where('visitor_id', $tourVisit['visitor_id'])->where('route_id',  $visitor['id'])->delete();

                $dates = [];
                foreach ($visitor['dates'] as $date) {
                    $dates[] = Carbon::createFromTimestamp($date)->toDateString();

                    $findTourVisitors = TourVisitors::where('visitor_id', $tourVisit['visitor_id'])->where('route_id', $visitor['id'])->whereDate('date', Carbon::createFromTimestamp($date)->toDateString())->first();

                    if (!empty($findTourVisitors)) {
                        throw new CoreException("ویزیتور " . $tourVisit['visitor_id'] . " با مسیر " . $visitor['id'] . " و تاریخ " . (new Verta($date))->formatDate() . " تکراری می باشد");
                    }
                }

                if (count($dates) != count(array_unique($dates))) {
                    throw new CoreException("تاریخ های ویزیتور " . $tourVisit['visitor_id'] . " با مسیر " . $visitor['id'] . "تکراری می باشد");
                }
            }

            if (count($inputRoutes) != count(array_unique($inputRoutes))) {
                throw new CoreException("مسیر ورودی ویزیتور {$tourVisit['visitor_id']}  تکراری می باشند");
            }
        }

        if (count($inputVisitors) != count(array_unique($inputVisitors))) {
            throw new CoreException("ویزیتور های ورودی تکراری می باشند");
        }
        DB::beginTransaction();
        try {
            foreach ($payload['tour_visits'] as $tourVisit) {
                foreach ($tourVisit['routes'] as $visitor) {

                    foreach ($visitor['dates'] as $date) {
                        TourVisitors::create([
                            'company_id' => auth('api')->id(),
                            'visitor_id' => $tourVisit['visitor_id'],
                            'route_id' => $visitor['id'],
                            'date' => Carbon::createFromTimestamp($date)->toDateString()
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->modelResponse(['data' => $payload, 'count' => 0]);
        } catch (\Exception $e) {
            throw new CoreException("خطا در ثیت عملیات" . $e->getMessage());
        }
    }
    public function delete(Request $request)
    {
        if (isset($request->visitor_id) && isset($request->route_id)) {
            TourVisitors::where('visitor_id', $request->visitor_id)->where('route_id', $request->route_id)->delete();
            return [
                'status' => true,
                'message' => "با موفقیت حذف شد",
                'id' => "",
            ];
        }
        return [
            'status' => false,
            'message' => "ایدی مسیر و ایدی روتر اجباری می باشد",
            'id' => "",
        ];
    }
}
