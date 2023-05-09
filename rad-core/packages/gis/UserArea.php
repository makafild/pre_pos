<?php


namespace Core\Packages\gis;


use App\BaseModel;
use Core\System\Http\Traits\HelperTrait;

class UserArea extends BaseModel
{
    public $timestamps = true;
    private static $_instance = null;
    protected $table = 'user_area';

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new UserArea();
        }
        return self::$_instance;
    }

}
