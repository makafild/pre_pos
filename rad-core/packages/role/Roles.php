<?php

namespace Core\Packages\role;

use Core\System\Exceptions\CoreException;
use App\BaseModel;
use DB;
use Core\Packages\user\Users;
use Carbon\Carbon;

use Core\Packages\role\PermissionsPanel;
use Core\Packages\role\RolePermissions;
use Core\System\Http\Traits\HelperTrait;
use Illuminate\Support\Facades\Input;

class Roles extends BaseModel
{
    use HelperTrait;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new Roles();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function rolePermission()
    {
        return $this->hasMany(RolePermissions::class, 'role_id', 'id');
    }

    public function permissionRoutes()
    {
            $permissionRoutes = PermissionsPanel::orderBy('route_name');
        if (auth('api')->user()['kind'] == 'superAdmin') {
            $permissionRoutes=$permissionRoutes->get();
        } else {
            $result=UserRoles::with('permission')
                ->where('user_id',auth('api')->id())->get();
                $userPermissionCodes=[];
            if($result->isNotEmpty()){
                foreach ($result as $row){
                    foreach ($row->permission as $permission){
                        $userPermissionCodes[]=$permission->permission_code;
                    }
                }
            }
            $permissionRoutes=$permissionRoutes->whereIn('code',array_unique($userPermissionCodes))->get();
        }
        return $this->modelResponse(['data' => $permissionRoutes, 'count' => count($permissionRoutes)]);
    }


    public function list($roleId = '')
    {
        $query = Roles::select(['id','name'])
            //->with('rolePermission')
            ->with('rolePermission.permission:code,name,route_name')
	    ->where('user_id',auth('api')->id());
        if (!empty(request()->input('role_id')) || !empty($roleId)) {
            if (empty($roleId)) {
                $roleId = request()->input('role_id');
            }
            $query->where('role_id', $roleId);
        }
        return $this->modelResponse(['data' => $query->get(), 'count' => $query->count()]);
    }

    public function show($roleId)
    {
        return $this->list($roleId);
    }

    public function store($payload)
    {
        $permissionsResult = PermissionsPanel::select('code', 'name', 'route_name')->get();
        $permissions = [];
        foreach ($permissionsResult as $permission) {
            $permissions[] = $permission['code'];
        }

        foreach ($payload['permissions'] as $permissionInput) {
            if (!in_array($permissionInput, $permissions)) {
                throw new CoreException("پرمیژن {$permissionInput} معتبر نمی باشد");
            }
        }

        $checkDuplicate=Roles::where('user_id',auth('api')->id())
        ->where('name', $payload['name'])->first();

        if(!empty( $checkDuplicate)){
            throw new CoreException("رکورد مورد نظر تکراری می باشد");
        }
        try {

            $roleStore = Roles::create(['name' => $payload['name'],'user_id'=>auth('api')->id()]);
            $data = [];
            foreach ($payload['permissions'] as $permission) {
                $data[] = [
                    'role_id' => $roleStore->id,
                    'permission_code' => $permission,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
            RolePermissions::insert($data);

            return $this->modelResponse(['data' => $data]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }

    }

    public function updateRow($payload, $roleId)
    {
        $findRole = Roles::find($roleId);
        if (!isset($findRole)) {
            throw new CoreException(' شناسه نقش ' . $roleId . ' یافت نشد');
        }

        $permissionsResult = PermissionsPanel::select('code', 'name', 'route_name')->get();
        $permissions = [];
        foreach ($permissionsResult as $permission) {
            $permissions[] = $permission['code'];
        }

        foreach ($payload['permissions'] as $permissionInput) {
            if (!in_array($permissionInput, $permissions)) {
                throw new CoreException('پرمیژن مورد نظر معتبر نمی باشد');
            }
        }

        try {
            $findRole->update(['name' => $payload['name']]);
            $data = [];
            foreach ($payload['permissions'] as $permission) {
                $data[] = [
                    'role_id' => $roleId,
                    'permission_code' => $permission,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }

            RolePermissions::where('role_id', $roleId)->delete();
            RolePermissions::insert($data);

            return $this->modelResponse(['data' => $data]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }

    }

    public function assignRolesToUser($payload, $userId)
    {
        $searchRoles = Roles::whereIn('id', $payload['role_ids'])->pluck('id');
        if ($searchRoles->isEmpty()) {
            $roleIds = implode(' و ', $payload['role_ids']);
            throw new CoreException(' شناسه نقش ' . $roleIds . ' یافت نشد');
        }

        if (count($payload['role_ids']) != count(array_unique($payload['role_ids']))) {
            throw new CoreException(' شناسه نقش تکراری می باشد');
        }

        if (count($searchRoles) != count($payload['role_ids'])) {
            $roleIds = implode(' و ', array_diff($payload['role_ids'], $searchRoles->toArray()));
            throw new CoreException(' شناسه نقش ' . $roleIds . ' یافت نشد');
        }

        $findUser = Users::find((int)$userId);
        if (!isset($findUser)) {
            throw new CoreException(' شناسه کاربر ' . $userId . ' یافت نشد');
        }

        $data = [];
        foreach ($payload['role_ids'] as $roleId) {
            $data[] = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        try {
            UserRoles::where('user_id', $findUser['id'])->delete();
            UserRoles::insert($data);
            return $this->modelResponse(['data' => $data]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    public function userRolesShow($userId)
    {
        $findUser = Users::find($userId);
        if (!isset($findUser)) {
            throw new CoreException('  کاربر ' . $userId . ' یافت نشد');
        }

        $query = UserRoles::select(['user_id', 'role_id'])
            ->with('role:id,name', 'user')

            ->where('user_id', $userId);

//        if (!empty(request()->input('user_id'))) {
//            $query->where('user_id', request()->input('user_id'));
//        }
        return $this->modelResponse(['data' => $query->get(), 'count' => $query->count()]);
    }

    public function systemRoutes()
    {
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
        $listRoutes = [];

        foreach ($routeCollection as $route) {
            $listRoutes[] = $route->getName();
        }

        return $listRoutes;
    }


}
