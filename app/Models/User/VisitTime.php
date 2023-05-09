<?php

namespace App\Models\User;

use Core\Packages\order\Order;
use Core\Packages\order\Visi;
use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;

class VisitTime extends Model
{
    protected $table="visit_time";


    public function Customer()
    {
        return $this->hasOne(Users::class, 'id','user_id');

    }
    public function Orders()
    {
        return $this->hasMany(Order::class, 'customer_id','user_id');
    }
    public function ReasonForNotVisiting()
    {
        return $this->hasMany(ReasonForNotVisiting::class, 'customer_id','user_id');
    }



    public function visitor()
    {
        return $this->hasOne(Users::class, 'id','visitor_id');
    }


}
