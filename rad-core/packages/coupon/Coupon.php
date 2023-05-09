<?php

namespace Core\Packages\Coupon;

use Core\Packages\user\Users;
use Core\Packages\order\Order;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\SecureDelete;
use Illuminate\Database\Eloquent\SoftDeletes;


class Coupon extends Model
{
    use SecureDelete;
    use Filterable;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */


    public function company()
    {
        return $this->belongsToMany(Users::class,'company_coupons', 'coupon_id' ,'company_id'  );
    }
    public function Orderss()
    {
        return $this->hasMany(Order::class , 'company_id');
    }

use  SoftDeletes;
}
