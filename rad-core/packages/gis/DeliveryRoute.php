<?php

namespace Core\Packages\gis;

use Core\Packages\user\Users;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\SecureDelete;
use Core\Packages\tour_delivery\TourDelivery;

class DeliveryRoute extends Model
{
    use SecureDelete;
    use Filterable;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */

     public function Users(){
         return $this->belongsToMany(Users::class ,'delivery_route_user' ,'route_id' , 'user_id');
     }

     public function Areas(){
         return $this->hasOne(areas::class , 'id' , 'area_id');
     }
     public function tours(){
        return $this->hasMany(TourDelivery::class , 'route_id');
    }
     protected $fillable = [
        'area_id',
        'route',
        'company_id'

     ];

}
