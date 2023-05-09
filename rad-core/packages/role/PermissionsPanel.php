<?php

namespace Core\Packages\role;

use Core\System\Exceptions\CoreException;

use App\BaseModel;
use DB;
use Carbon\Carbon;


use Core\System\Http\Traits\HelperTrait;

class PermissionsPanel extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;
    protected $table = 'permissions_panel';

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new PermissionsPanel();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'code',
        'name',
        'route'
    ];


    public function getAllRoutes()
    {
        $routeCollection = $this->select('code','name','route_name')->get();
        return $routeCollection;
    }

    public function permissionRoutes()
    {
        $result = $this->getAllRoutes();
        return $this->modelResponse(['data' => $result, 'count' => count($result)]);
    }

    public function list()
    {
        $query = $this->select(['role_id', 'name'])
            ->with('role:id,name');
        if (!empty(request()->input('role_id'))) {
            $query->where('role_id', request()->input('role_id'));
        }
        return $this->modelResponse(['data' => $query->get(), 'count' => $query->count()]);
    }

}
