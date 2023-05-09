<?php


namespace Core\Packages\gis;


use DB;
use App\User;
use App\BaseModel;
use Core\Packages\user\Users;
use Illuminate\Routing\Route;
use EloquentFilter\Filterable;
use Core\Packages\visitor\Visitors;
use Core\System\Http\Traits\HelperTrait;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\SecureDelete;

class Routes extends BaseModel
{
    use SecureDelete;
    use HelperTrait;
    use Filterable;
    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Routes();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'province_id',
        'city_id',
        'area_id',
        'route'
    ];

    public function list($id='')
    {
        $query = $this->orderBy('id', 'desc');

        if (!empty($id)) {
            $result = $this->find($id);
            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $result = $query->where('id', $id)->first();
        } else {
            $result = $query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id)?1:count($result)]);
    }

    public function validate($payload)
    {
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

    public function Visitors()
    {
        return $this->belongsToMany(Visitors::class, 'visitor_route','route_id','visitor_id');
    }
    public function User()
    {
        return $this->belongsToMany(Routes::class, 'user_route', "route_id","user_id");
    }

    public function Users()
    {
        return $this->belongsToMany(Users::class, 'user_route', "route_id","user_id");
    }

    public function store($payload)
    {
        $this->validate($payload);
        $route = $this->create($payload);
        $user = auth('api')->user()->id;
        $route->User()->sync($user);
        if (isset($payload['visitors'])){
            $route->Visitors()->sync($payload['visitors']);
        }
        if (isset($payload['customer_ids'])){
            $customers = Users::where('users.kind', Users::KIND_CUSTOMER)->whereIn('users.id', $payload['customer_ids'])
            ->select('users.*')->with(['Routes'])->get();
          //  $route->Customers()->toggle($payload['customer_ids']);
            foreach ($customers as $customer) {
                $customer->Routes()->detach();
                $customer->Routes()->toggle($route->id);
            }
        }
        return $route;




    }

    public function updateU($payload, $id)
    {
        $this->validate($payload);
        $this->updateRow($payload,$id,false);
        $route = Routes::find($id);
        if (isset($payload['visitors'])){
            $route->Visitors()->sync($payload['visitors']);
        }
        return $route;
    }

    public function area()
    {
        return $this->belongsTo(Areas::class);
    }
    public function Customers()
    {
        return $this->belongsToMany(Users::class, 'user_route', "route_id",'user_id' )->where('kind',Users::KIND_CUSTOMER);
    }
    public function getToatalCustomers()
    {
        return $this->belongsToMany(Users::class, 'user_route', "route_id",'user_id' )->where('kind',Users::KIND_CUSTOMER)->count();
    }
    public function Tour()
    {
        return $this->hasMany(Users::class,"route_id");
    }
}
