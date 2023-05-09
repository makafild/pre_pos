<?php

namespace Core\Packages\shop;

use Illuminate\Database\Eloquent\Model;
use Core\Packages\user\Users;
class ShopDeliveryDates extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */

    protected $table = 'geo_date_delivery' ;

    public function times(){
        return $this->hasMany(ShopDeliveryTime::class,'date_id' );
     }
}
