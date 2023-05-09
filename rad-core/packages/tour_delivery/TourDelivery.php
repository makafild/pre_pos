<?php

namespace Core\Packages\tour_delivery;
use App\BaseModel;
use Core\Packages\user\Users;
use Core\Packages\gis\DeliveryRoute;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\HelperTrait;
use Core\Packages\tour_delivery\DeliveryDates;
use GuzzleHttp\Psr7\Request;

class TourDelivery extends BaseModel
{
    use HelperTrait;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $table = 'tour_delivery' ;

    protected $fillable =["route_id","company_id","post_id"];
     public function dates(){
      return  $this->hasMany(DeliveryDates::class,'delivery_tour_id');
     }


     public function routes(){
       return $this->hasOne(DeliveryRoute::class , 'id' , 'route_id');
     }
     public function company(){
        return $this->belongsTo(Users::class , 'company_id' , 'id');
      }
     public $timestamps = true;
    private static $_instance = null;


     public static function _()
     {
         if (self::$_instance == null) {
             self::$_instance = new TourDelivery();
         }
         return self::$_instance;
     }
     public function list($request)
    {

        $query = TourDelivery::with('routes.Areas' , 'company')->leftjoin('delivery_dates'  , 'tour_delivery.id' ,'=' ,'delivery_dates.delivery_tour_id' )
       ->leftjoin('delivery_time' , 'delivery_dates.id' , '=' , 'delivery_time.date_times_id')->leftjoin('delivery_routes' , 'tour_delivery.route_id' , '=' ,'delivery_routes.id');

       if ($request->area_id) {
        $area = $request->area_id;
        $query->whereHas('routes.Areas', function ($q) use ($area) {
            return $q->where('id', $area);
        });
    }
    if ($request->city_id) {
        $city_id = $request->city_id;
        $query->whereHas('routes.Areas.city', function ($q) use ($city_id) {
            return $q->where('id', $city_id);
        });

    }
        if ($this->ISCompany()) {
            $query->where('tour_delivery.company_id',$this->ISCompany());
        }
        // if (!empty(request()->input('visitor_id'))) {
        //     $query = $query->where('visitor_id', request()->input('visitor_id'));
        // }

        if (!empty(request()->input('route_id'))) {
            $query = $query->where('tour_delivery.route_id', request()->input('route_id'));
        }

        $query = $query->get();


        return $this->modelResponse(['data' => $query, 'count' => $query->count()]);
    }





}
