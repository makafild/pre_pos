<?php


namespace Core\Packages\gis;


use DB;
use App\BaseModel;
use Core\Packages\user\Users;
use EloquentFilter\Filterable;

use Core\System\Http\Traits\HelperTrait;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\SecureDelete;

class Areas extends BaseModel
{
    
    use SecureDelete;
    use HelperTrait;
    use Filterable;
    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Areas();
        }
        return self::$_instance;
    }
    protected $appends =[
      'province_name',
      'city_name',
    ];
    protected $fillable = [
        'province_id',
        'city_id',
        'area'
    ];
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function province()
    {
        return $this->belongsTo(Province::class)->with('Country');
    }
    public function Routes()
    {
        return $this->hasMany(Routes::class,"area_id");
    }
    public function Users()
    {
        return $this->belongsToMany(Users::class, 'user_area','area_id','user_id');
    }
    public function list($id='')
    {
        $query = $this->orderBy('id', 'desc');

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result=$query->where('id',$id)->first();
        }else{
            $result=$query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id)?1:count($result)]);
    }

    public function validate($payload){
        $findProvince = DB::table('provinces')->where('id', $payload['province_id'])->first();
        if (empty($findProvince)) {
            throw new CoreException(' شناسه استان ' . $payload['province_id'] . ' یافت نشد');
        }

        $findCity= DB::table('cities')->where('id', $payload['city_id'])->first();
        if (empty($findCity)) {
            throw new CoreException(' شناسه شهرستان ' . $payload['city_id'] . ' یافت نشد');
        }

        if ($findCity->province_id != $payload['province_id']) {
            throw new CoreException('شناسه شهرستان با شناسه استان مطابقت ندارد');
        }
    }

    public function store($payload,$company_id)
    {
        $this->validate($payload);
        $route = $this->create($payload);
         $route->Users()->sync($company_id);
    }

    public function updateU($payload, $id)
    {
        $this->validate($payload);
        return $this->updateRow($payload,$id);
    }
    public function destroyRecord($id){
        return $this->destroyRow($id);
    }
    public function getProvinceNameAttribute(){
       if ($this->province){
         return $this->province->name;
       }
    }
    public function getCityNameAttribute(){
       if ($this->city){
         return $this->city->name;
       }
    }

}
