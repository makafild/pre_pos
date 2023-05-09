<?php

namespace Core\Packages\comment;

use App\BaseModel;

use App\Models\User\User;
use Core\Packages\product\Product;
use Core\Packages\role\PermissionsPanel;
use Core\Packages\role\RolePermissions;
use Core\Packages\user\Users;
use Core\System\Http\Traits\HelperTrait;
use Illuminate\Support\Facades\Input;
use EloquentFilter\Filterable;

class Comments extends BaseModel
{
    use HelperTrait;
    use Filterable;


    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Comments();
        }
        return self::$_instance;
    }

    public function user()
    {
        return $this->belongsTo(Users::class)->withTrashed();
    }
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function answerUser()
    {
        return $this->belongsTo(Users::class,'answer_user_id');
    }

    public function like()
    {
        return $this->hasMany(CommentRates::class,'comment_id')->where('action', '=', 'like');
    }


    public function dislike()
    {
        return $this->hasMany(CommentRates::class,'comment_id')->where('action', '=', 'dislike');
    }

    public function company()
    {
        return $this->belongsTo(User::class,'company_id');
    }

}
