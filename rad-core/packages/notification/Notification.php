<?php

namespace Core\Packages\notification;

use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Core\Packages\gis\Province;
use Core\Packages\common\Constant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    protected $table="notifications";

    const KIND_LINK    = 'link';
	const KIND_COMPANY = 'company';
	const KIND_PRODUCT = 'product';

	const KINDS = [
		self::KIND_LINK,
		self::KIND_COMPANY,
		self::KIND_PRODUCT,
	];




   public function City()
   {
    return $this->belongsToMany(City::class, 'city_notification', 'notification_id', "city_id");
   }

   public function Provinces()
   {
    return $this->belongsToMany(Province::class,  'province_notification', 'notification_id', "province_id");
   }
   public function Areas()
   {
    return $this->belongsToMany(Areas::class,  'area_notification', 'notification_id', "area_id");
   }
   public function Routes()
   {
    return $this->belongsToMany(Routes::class,  'route_notification', 'notification_id', "route_id");
   }
   public function Price_classes()
   {
    return $this->belongsToMany(Constant::class,  'priceclass_notification', 'notification_id', "constant_id");
   }
   public function customer()
   {
    return $this->belongsToMany(Users::class,  'user_notification', 'notification_id', "user_id");
   }

}
