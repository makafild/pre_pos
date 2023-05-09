<?php

namespace Core\Packages\comment;

use App\BaseModel;

use Core\Packages\role\PermissionsPanel;
use Core\Packages\role\RolePermissions;
use Core\Packages\user\Users;
use Core\System\Http\Traits\HelperTrait;
use Illuminate\Support\Facades\Input;

class CommentRates extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new CommentRates();
        }
        return self::$_instance;
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }

}
