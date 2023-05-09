<?php


namespace Core\Packages\visitor_position;

use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;

class VisitorPosition extends Model
{

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new VisitorPosition();
        }
        return self::$_instance;
    }

}
