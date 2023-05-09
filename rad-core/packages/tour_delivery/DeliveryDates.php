<?php

namespace Core\Packages\tour_delivery;

use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;
use Core\Packages\tour_delivery\TourDelivery;

class DeliveryDates extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */

    protected $table = 'delivery_dates' ;

    public function times(){
        return $this->hasMany(DeliveryTime::class,'date_times_id');
     }

     public function TourDelivery(){
        return $this->hasOne(TourDelivery::class,'id','delivery_tour_id');
     }
}
