<?php

namespace Core\Packages\tour_visit;

use Core\Packages\gis\Routes;
use Core\Packages\tour_visit\TourRoutes;
use App\BaseModel;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\HelperTrait;
use DB;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Core\Packages\user\Users;
use Core\Packages\visitor\Visitors;

class TourVisitors extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new TourVisitors();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'visitor_id',
        'company_id',
        'route_id',
        'date'
    ];

    public function list()
    {

        $query = TourVisitors::with(['Visitor', 'Route', 'Company'])->whereHas('Route');

        if ($this->ISCompany()) {
            $query->where('company_id',$this->ISCompany());
        }
        if (!empty(request()->input('visitor_id'))) {
            $query = $query->where('visitor_id', request()->input('visitor_id'));
        }

        if (!empty(request()->input('route_id'))) {
            $query = $query->where('route_id', request()->input('route_id'));
        }

        $query = $query->get();

        return $this->modelResponse(['data' => $query, 'count' => $query->count()]);
    }

    public function show($visitorId)
    {
        $query = TourVisitors::
        where('visitor_id', $visitorId)->
        where('company_id', auth('api')->user()->company_id)->
        with(['Visitor', 'Route', 'Company'])->get();

        return $this->modelResponse(['data' => $query, 'count' => $query->count()]);
    }

    public function store($payload)
    {
        $inputVisitors = [];
        foreach ($payload['tour_visits'] as $tourVisit) {
            $inputVisitors[] = $tourVisit['visitor_id'];
            $inputRoutes = [];
            foreach ($tourVisit['routes'] as $visitor) {
                TourVisitors::where('visitor_id',$tourVisit['visitor_id'])->where('route_id',$visitor['id'])->delete();
                $inputRoutes[] = $visitor['id'];
                $dates = [];
                foreach ($visitor['dates'] as $date) {
                    $dates[] = Carbon::createFromTimestamp($date)->toDateString();

                    $findTourVisitors = TourVisitors::
                    where('visitor_id', $tourVisit['visitor_id'])->
                    where('route_id', $visitor['id'])->
                    whereDate('date', Carbon::createFromTimestamp($date)->toDateString())->
                    first();

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
                            'company_id' => auth('api')->user()->company_id,
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

    public function updateR($payload)
    {
        $findVisitor = TourVisitors::
        where('visitor_id', $payload['visitor_id'])->
        where('route_id', $payload['route_id'])->
        where('company_id',auth('api')->user()->company_id)->
        get();



        if (empty($findVisitor)) {
            throw new CoreException("با اطلاعات وارد شده رکوردی یافت نشد");
        }

        foreach ($payload['dates'] as $date) {

            $dates[] = Carbon::createFromTimestamp($date)->toDateString();

            if (!empty($findTourVisitors)) {
                throw new CoreException(" تاریخ " . (new Verta($date))->formatDate() . " تکراری می باشد");
            }
        }

        DB::beginTransaction();
        try {

            TourVisitors::
            where('visitor_id', $payload['visitor_id'])->
            where('route_id', $payload['route_id'])->
            where('company_id', auth('api')->user()->company_id)->
            delete();

            foreach ($payload['dates'] as $date) {
                TourVisitors::create([
                    'company_id' => auth('api')->user()->company_id,
                    'visitor_id' => $payload['visitor_id'],
                    'route_id' => $payload['route_id'],
                    'date' => Carbon::createFromTimestamp($date)->toDateString()
                ]);
            }

            DB::commit();
            return $this->modelResponse(['data' => $payload, 'count' => 0]);

        } catch (\Exception $e) {
            throw new CoreException("خطا در ثیت عملیات" . $e->getMessage());
        }
    }

    public function Visitor()
    {
        return $this->belongsTo(Users::class, 'visitor_id')->withTrashed();
    }

    public function Route()
    {
        return $this->belongsTo(Routes::class, 'route_id');
    }

    public function Company()
    {
        return $this->belongsTo(Users::class, 'company_id');
    }


}
