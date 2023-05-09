<?php

namespace Core\System\Http\Middleware;

use DB;
use Closure;
use Core\Packages\user\Users;
use Core\Packages\role\UserRoles;
use Core\Packages\group\FaRouterSystem;
use Core\Packages\role\RolePermissions;
use Core\Packages\role\PermissionsPanel;
use Core\System\Exceptions\CoreException;

class Acl
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $hasAccess = false;
        //        $userInfo = auth('api')->user()->toArray();
        $userInfo = Users::with('Group')->where('id', auth('api')->id())->first();

        if ($userInfo['status'] == 'inactive') {

            auth('api')->logout();
        }

        $farsi="";
        if ($userInfo['kind'] == 'superAdmin') {
            $hasAccess = true;
        } else {

            $request_route = explode("\\", $request->route()->getActionName());
            $request_route = "W".$request_route[5];
            $farsi =  $request_route;
            if (isset($userInfo->group->access)) {

                $array_access = json_decode($userInfo->group->access);

                if (in_array($request_route, $array_access)){
                    $hasAccess = true;
                  }  else{
                    $farsi = FaRouterSystem::select('fa')->where('en', $request_route)->first();

                    $hasAccess = false;
                }
            } else{
                $hasAccess = false;
                $farsi = FaRouterSystem::select('fa')->where('en', $request_route)->first();
            }
        }


        /* $roles = UserRoles::where('user_id', $userInfo['id'])->pluck('role_id')->toArray();
        $routeName = $request->route()->getName();
        if (count($roles)) {
            $permissions = RolePermissions::with('permission')->whereIn('role_id', $roles)->get();
            $allPermissions = [];
            foreach ($permissions->toArray() as $permission) {
                if (isset($permission['permission']['route_name'])) {
                    $allPermissions[] = $permission['permission']['route_name'];
                }
            }
            if (count($allPermissions)) {
                if (in_array($routeName, $allPermissions) == true) {
                    $hasAccess = true;
                }
            }
        }*/


        if (!$hasAccess) {
            if(!$farsi) $farsi=$request_route;
           /* $permissions = PermissionsPanel::pluck('name', 'route_name');
            $translateName = !empty($permissions[$routeName]) ? $permissions[$routeName] : $routeName;*/
            $message = "دسترسی شما به ".$farsi."محدود می باشد";
            //return response(['hasError' => true, 'message' => [$message]], 402);

            throw new CoreException( $message);

        }
        return $next($request);
    }
}
