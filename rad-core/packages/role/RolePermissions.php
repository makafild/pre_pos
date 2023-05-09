<?php

namespace Core\Packages\role;

use Core\System\Exceptions\CoreException;

use App\BaseModel;
use DB;
use Carbon\Carbon;


use Core\System\Http\Traits\HelperTrait;

class RolePermissions extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new RolePermissions();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'role_id',
        'permission'
    ];

protected $hidden = ['role_id','created_at','updated_at'];

    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'id');
    }
    public function permission()
    {
        return $this->belongsTo(PermissionsPanel::class, 'permission_code', 'code');
    }



    public function store($payload, $roleId)
    {
        $findRole = Roles::find($roleId);
        if (!isset($findRole)) {
            throw new CoreException(' شناسه نقش ' . $roleId . ' یافت نشد');
        }

        $getAllRoutes = $this->getAllRoutes();

        $allRoutes = [];
        foreach ($getAllRoutes as $route) {
            $allRoutes[] = $route['name'];
        }

        foreach ($payload['permissions'] as $permission) {
            if (in_array($permission, $allRoutes) == false) {
                throw new CoreException(' شناسه ' . $permission . ' یافت نشد');
            }
        }


        $data = [];
        foreach ($payload['permissions'] as $permission) {
            $data[] = [
                'role_id' => $roleId,
                'name' => $permission,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        try {
            $this->where('role_id', $roleId)->delete();
            $this->insert($data);
            return $this->modelResponse(['data' => $data]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }
}
