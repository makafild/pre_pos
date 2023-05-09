<?php

namespace core\Packages\version\src\controllers;




use Illuminate\Http\Request;
use Core\Packages\version\versionInfo;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\version\src\request\StoreRequest;
use Core\Packages\version\src\request\UpdateRequest;
use Core\Packages\version\src\request\DestroyRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class VersionPackageController extends CoreController
{


    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {


        $List_versions = versionInfo::select('*')->orderBy('created_at', 'DESC');

        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $customer = $List_versions->get();
        } else {
            $customer = $List_versions->jsonPaginate($limit);
        }

        return $customer ;
    }

    public function store(StoreRequest $request)
    {

        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $version = new versionInfo();
            $version->version = $request->version;
            $version->description = $request->description;
            $version->save();
            return [
                'status' => true,
                'message' => trans('messages.api.version.store'),
            ];
        } else {
            return [
                'status' => false,
                'message' => trans('auth.permissation'),
            ];
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {

            $version = versionInfo::find($id);
            if (!isset($version)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $version->version = $request->version;
            $version->description = $request->description;
            $version->save();
            return [
                'status' => true,
                'message' => trans('messages.api.version.update'),
            ];
        } else {
            return [
                'status' => false,
                'message' => trans('auth.permissation'),
            ];
        }
    }

    public function show($id)
    {

        $version = versionInfo::find($id);

        if (!isset($version)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }

        return $version->toJson();
    }

    public function destroy(DestroyRequest $request)
 {

     $version=versionInfo::find($request->id);

     if (!isset($version)) {
         throw new CoreException(' شناسه ' . $request->id . ' یافت نشد');
     }
     $version->delete();

     return [
         'status' => true,
         'message' => trans('messages.api.version.destore'),
     ];
 }

 /* @param  int  $id
     * @return \Illuminate\Http\Response
     */

}
