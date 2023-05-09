<?php

namespace Core\Packages\shop;

use App\Models\Order\OrderCompanyPriorities;
use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;




class Visi extends Model
{
    protected $table="visitors";

    public function User()
    {
        return $this->hasOne(Users::class, 'id','user_id');

    }


}
