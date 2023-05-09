<?php


namespace Core\Packages\gis;


use Core\Packages\user\Users;
use Core\System\Exceptions\CoreException;
use App\BaseModel;
use DB;

use Core\System\Http\Traits\HelperTrait;

class Points extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Points();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'route_id',
        'user_id',
        'lat',
        'lan',
        'state'
    ];
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
        $findRoute =Routes::find($payload['route_id'] );
        $findUser =Users::find($payload['user_id'] );
        if (!isset($findRoute)) {

            throw new CoreException(' شناسه مسیر ' . $payload['route_id'] . ' یافت نشد');
        }
        if (!isset($findUser)) {
            throw new CoreException(' شناسه کاربری ' . $payload['user_id'] . ' یافت نشد');
        }
    }

    public function store($payload)
    {
        $this->validate($payload);
        return $this->insertRow($payload);
    }

    public function updateU($payload, $id)
    {
        $this->validate($payload);
        return $this->updateRow($payload,$id);
    }
    public function destroyRecord($id){

        return $this->destroyRow($id);
    }
    public function User()
    {
        return $this->belongsTo(Users::class);
    }
}
