<?php

namespace core\Packages\role\src\controllers;

use Core\System\Http\Controllers\CoreController;
use Core\Packages\role\src\request\RoleRequest;
use Core\Packages\role\src\request\PermissionRoleRequest;
use Core\Packages\role\src\request\UserRoleRequest;
use Core\Packages\role\Roles;

/**
 * Class RolePackageController
 *
 * @package Core\Packages\role\src\controllers
 */


class RolePackageController extends CoreController
{

    private $_role = [
        'name',
        'permissions'
    ];

    private $_user = [
        'role_ids'
    ];


    public function list(){
        $result = Roles::_()->list();
        return $this->responseHandler2($result);
    }

    public function show($roleId){
        $result = Roles::_()->show($roleId);
        return $this->responseHandler2($result);
    }

    public function store(RoleRequest $request){
    
        $payload = $request->only($this->_role);
        $result = Roles::_()->store($payload);
        return $this->responseHandler2($result);
    }

    public function update(RoleRequest $request,$id){
        $payload = $request->only($this->_role);
        $result = Roles::_()->updateRow($payload,$id);
        return $this->responseHandler($result);
    }
//
    public function assign_roles_to_user(UserRoleRequest $request, $userId){
        $payload = $request->only($this->_user);
        $result = Roles::_()->assignRolesToUser($payload,$userId);
        return $this->responseHandler($result);
    }

    public function permission_routes(){
        $result = Roles::_()->permissionRoutes();
        return $this->responseHandler2($result);
    }

    public function system_routes(){
        $result = Roles::_()->systemRoutes();
        return $this->responseHandler2($result);
    }

   public function user_roles_show($userId){
        $result = Roles::_()->userRolesShow($userId);
        return $this->responseHandler2($result);
    }
}
