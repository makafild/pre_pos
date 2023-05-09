<?php

namespace App\Traits;


use Core\Packages\role\RolePermissions;
use Core\Packages\role\UserRoles;

trait CheckAccess
{
    public function chAc($user_id, $routeCode = null)
    {
        return true;//TODO
        $hasAccess = false;
        $roles = UserRoles::where('user_id', $user_id)->pluck('role_id')->toArray();

        if (is_null($routeCode)) {
            $routeName = \Request::route()->getName();
        }

        if (count($roles)) {
            $permissions = RolePermissions::with('permission')->whereIn('role_id', $roles)->get();
            $allPermissionsName = [];
            $allPermissionsCode = [];
            foreach ($permissions->toArray() as $permission) {
                if (isset($permission['permission']['route_name'])) {
                    $allPermissionsName[] = $permission['permission']['route_name'];
                    $allPermissionsCode[] = $permission['permission']['code'];
                }
            }
            if (count($allPermissionsName)) {
                if (in_array($routeName, $allPermissionsName) == true) {
                    $hasAccess = true;
                }
            }
            if (count($allPermissionsCode)) {
                if (in_array($routeCode, $allPermissionsCode) == true) {
                    $hasAccess = true;
                }
            }
        }
        return $hasAccess;
    }
}
