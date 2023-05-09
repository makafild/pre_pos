<?php


namespace Core\Packages\role;
use Core\Packages\user\Users;

use Carbon\Carbon;

use App\BaseModel;
use Core\System\Http\Traits\HelperTrait;

class UserRoles extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new UserRoles();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'user_id',
        'role_id'
    ];

    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function permission()
    {
        return $this->hasMany(RolePermissions::class, 'role_id', 'role_id');
    }
}
