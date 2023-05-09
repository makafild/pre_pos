<?php


namespace Core\Packages\gis;


use App\BaseModel;
use Core\System\Http\Traits\HelperTrait;

class UserRoute extends BaseModel
{
    public $timestamps = true;
    private static $_instance = null;
    protected $table = 'user_route';

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new UserRoute();
        }
        return self::$_instance;
    }

    public  function RouteInfo(){
        return $this->belongsTo(Routes::class, 'route_id');
    }

}
