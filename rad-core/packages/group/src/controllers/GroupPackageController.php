<?php

namespace core\Packages\group\src\controllers;




use Illuminate\Http\Request;
use Core\Packages\group\Group;
use Illuminate\Support\Facades\Route;
use Core\Packages\group\FaRouterSystem;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\group\src\request\StoreRequest;
use Core\Packages\group\src\request\UpdateRequest;
use Core\Packages\group\src\request\DestroyRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class GroupPackageController extends CoreController
{


    /*public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        $List_groups = Group::select('*')->orderBy('created_at', 'DESC')->get();

        $customer = array();
        foreach ($List_groups as $group) {
            $farsi = FaRouterSystem::whereIn('en', json_decode($group->access))->get();
            $group->access2 = $farsi;
            $customer[] = $group;
        }
        return $customer;
    }
*/
public function list(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        $List_groups = Group::select('*')->orderBy('created_at', 'DESC')->get();

        $customer = array();
        foreach ($List_groups as $group) {
            $farsi = FaRouterSystem::whereIn('en', json_decode($group->access))->get();
            $group->access2 = $farsi;
            $customer[] = $group;
        }
        return $customer;
    }


    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

       return Group::select('*')->orderBy('created_at', 'DESC')->jsonPaginate( $limit);
    }
    public function store(StoreRequest $request)
    {

        $group = new Group();
        $group->salt = auth('api')->user()->company_id;
        $group->name = $request->name;
        $group->for = $request->for;

        if ($this->ISUsre()) {
            $user_group = Group::find($this->ISUsre()->group_id);
            $temp = $this->array_only(json_decode($user_group->access), $request->access);
            foreach ($this->deffualt_url() as $url) array_push($temp, $url);
            $group->access = json_encode($temp);
        } else {
            $temp1 = $request->access;
            foreach ($this->deffualt_url() as $url) array_push($temp1, $url);
            $group->access = json_encode($temp1);
        }
        $group->save();
        return [
            'status' => true,
            'message' => trans('سطح دسترسی با موفقیت ثبت شد'),
        ];
    }

    public function update(UpdateRequest $request, $id)
    {

        $group = Group::find($id);
        if (!isset($group)) {

            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }
        $group->name = $request->name;
        $group->for = $request->for;
        if ($this->ISUsre()) {
            $user_group = Group::find($this->ISUsre()->group_id);
            $group->access = json_encode($this->array_only(json_decode($user_group->access), $request->access));
        } else
            $group->access = json_encode($request->access);
        $group->save();
        return [
            'status' => true,
            'message' => trans('سطح دسترسی بروز شد'),
        ];
    }

    public function show($id)
    {

        $group = Group::find($id);

        if (!isset($group)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }
        $url_with_fa = array();
        /* foreach(json_decode($group->access) as $strurl){

            $farsi = FaRouterSystem::select('fa')->where('en',$strurl)->first();

            $url_with_fa['list_route'][] =
                    [
                        "url" =>   $strurl,
                        "fa" => ($farsi) ? $farsi['fa'] : $strurl
                    ];
        }*/

        $farsi = FaRouterSystem::whereIn('en', json_decode($group->access))->get();

        $group->access2 = $farsi;
        return $group->toJson();
    }

    public function destroy(DestroyRequest $request ,Group $go )
    {


        $group = Group::whereIn('id', $request->id);

        if (!isset($group)) {
            throw new CoreException(' شناسه ' . $request->id . ' یافت نشد');
        }
        $go->secureDelete($request->id , ['user']);

        return [
            'status' => true,
            'message' => trans('سطح دسترسی با موفقیت حذف شد'),
        ];
    }


    public function allRoutes()
    {
        $listRouteUser = array();
        $web = array();
        $app = array();
        $listRouters = array();
        $routes = Route::getRoutes();
        if ($this->ISUsre()) {
            $user_group = Group::find($this->ISUsre()->group_id);
            if (!isset($user_group))
                return [
                    "api_web" => null,
                    "api_app" => null,
                ];
            $listRouters = json_decode($user_group->access);
        }

      
        //list routes
        foreach ($routes->getRoutes() as $route) {
            //detected wich part mobile or panel
            if ($route->getActionName()[0] == 'A') {
                $key1 = explode("\\", $route->getActionName());
                if (!isset($key1[5])) continue;
                if ($this->ISUsre())
                    if (!in_array((isset($key1[7])) ? $key1[7] : $key1[6], $listRouters)) continue;
                $key = explode("@", (isset($key1[7])) ? $key1[7] : $key1[6]);
                if (!isset($app[$key[0]])) {
                    $app[$key[0]] = array();
                    $app[$key[0]]['list_route'] = array();
                }
                $name_category = FaRouterSystem::select('fa')->where('en', $key[0])->first();
                $app[$key[0]]["name"] = ($name_category) ? $name_category['fa'] : $key[0];
                $farsi = FaRouterSystem::select('fa')->where('en', (isset($key1[7])) ? $key1[7] : $key1[6])->first();
                $keyt = (isset($key1[7])) ? $key1[7] : $key1[6];
                if (!in_array($keyt, $this->blockRouteMobile()))
                    $app[$key[0]]['list_route'][] =
                        [
                            "url" => (isset($key1[7])) ? $key1[7] : $key1[6],
                            "fa" => ($farsi) ? $farsi['fa'] : ((isset($key1[7])) ? $key1[7] : $key1[6]),
                            "temp" =>  [$route->methods, $route->uri],
                            "ischecked" => false
                        ];
            } elseif ($route->getActionName()[0] == 'C') {
                $key1 = explode("\\", $route->getActionName());
                if (!isset($key1[5])) continue;
              
                if ($this->ISUsre())
                    if (!(in_array($key1[5], $listRouters) or in_array("W".$key1[5], $listRouters))) continue;

                $key = explode("@", $key1[5]);
               
                if (!isset($web[$key[0]])) {
                    $web[$key[0]] = array();
                    $web[$key[0]]['list_route'] = array();
                }
                $name_category = FaRouterSystem::select('fa')->where('en', $key[0])->first();
                $web[$key[0]]["name"] = ($name_category) ? $name_category['fa'] : $key[0];
                $farsi = FaRouterSystem::select('fa')->where('en', $key1[5])->first();
                if (!in_array($key1[5], $this->deffualt_url()))
                    $web[$key[0]]['list_route'][$key1[5]] =
                        [
                            "url" =>   "W".$key1[5],
                            "fa" => ($farsi) ? $farsi['fa'] : $key1[5],
                            "temp" =>  [$route->methods, $route->uri],
                            "ischecked" => false

                        ];
            } else {
            }
        }
        // array_filter($web,function($obj){
        //       if($)
        // });
        return [
            "api_web" => $this->getListArrayCategory($web),
            "api_app" => $app,
        ];
    }


    public function farsi(Request $request)
    {

        if (!$request->fa || !$request->en)
            throw new CoreException('نام فارسی و انگلیسی الزامیست');
        $farsi = FaRouterSystem::select('fa')->where('en', $request->en)->first();
        if ($farsi) {
            $update_Fa = FaRouterSystem::select('fa')->where('en', $request->en)->update(['fa' => $request->fa]);
        } else {
            FaRouterSystem::insert([
                'en' => $request->en,
                'fa' => $request->fa,
            ]);
        }

        return [
            'status' => true,
            'message' => trans('با موفقیت ثبت شد'),
        ];
    }

    /* @param  int  $id
     * @return \Illuminate\Http\Response
     */


    private function ISUsre()
    {
        if (auth('api')->user()['kind'] == 'superAdmin')
            return false;
        else
            return auth('api')->user();
    }

    private function array_only($array_1, $array_2)
    {

        $array_temp = array();
        foreach ($array_2 as $item) {
            if (in_array($item, $array_1))
                array_push($array_temp, $item);
        }

        return $array_temp;
    }

    private function deffualt_url()
    {
        return [
            'WUserPackageController@login',
            'WUserPackageController@logout',
            'WUserPackageController@profile',
            'WUserPackageController@refreshToken',
            'WCustomerPackageController@create',
            'WCustomerPackageController@edit',
            'WCustomerPackageController@states',
            'WCustomerPackageController@states',
            'WOrderPackageController@deliver',
            'WPromotionPackageController@create',
            'WPromotionPackageController@edit',
            'WPromotionPackageController@states',
            'WCompanyPackageController@city',
            'WCompanyPackageController@create',
            'WCompanyPackageController@edit',
            'WNewsPackageController@create',
            'WNewsPackageController@edit',
            'WPriceClassPackageController@states',
            'WPriceClassPackageController@create',
            'WPriceClassPackageController@edit',
            'WSliderPackageController@states',
            'WSliderPackageController@create',
            'WSliderPackageController@edit',
            'WVisitorPositionController@update',
            'WVisitorPositionController@list',
            'WVersionPackageController@create',
            'WVersionPackageController@store',
            'WVersionPackageController@show',
            'WVersionPackageController@edit',
            'WVersionPackageController@destroy',
            'WVersionPackageController@index',
            'WVersionPackageController@update',
            'WConstantPackageController@create',
            'WConstantPackageController@edit',
            'WConstantPackageController@listConstantCompany',
            'WProductPackageController@create',
            'WProductPackageController@edit',
            'WBrandPackageController@create',
            'WBrandPackageController@edit',
            'WPhotoPackageController@status',
            'WPhotoPackageController@index',
            'WPhotoPackageController@create',
            'WPhotoPackageController@show',
            'WPhotoPackageController@file',
            'WPhotoPackageController@edit',
            'WPhotoPackageController@update',
            'WPhotoPackageController@destroy',
            'WCouponPackageController@create',
            'WCouponPackageController@edit',
            'WgroupPackageController@create',
            'WgroupPackageController@edit',
            'WNotificationPackageController@create',
            'WNotificationPackageController@edit',
            'WGisPackageController@points_update',
            'WReportController@report_1',
            'WReportController@report_2',
            'WReportController@report_3',
            'WReportController@report_4',
            'WReportController@report_5',
            'WVisitorPackageController@states',
            'WVisitorPackageController@create',
            'WVisitorPackageController@edit',
            'WVisitorPackageController@unvisitedReport',

        ];
    }



    private function orderByCategory($list)
    {

        //   return $list;
        /*  foreach($list['OrderPackageController']['list_route'] as $key=> $route){
    unset($list['OrderPackageController']['list_route'][$key][])
     }
return $list;*/
    }





    private function getListArrayCategory($list)
    {

        if (isset($list['UserPackageController']['list_route']['WUserPackageController@loginAs'])) {
            $list['CompanyPackageController']['list_route']['WUserPackageController@loginAs'] = $list['UserPackageController']['list_route']['WUserPackageController@loginAs'];
            unset($list['UserPackageController']['list_route']['WUserPackageController@loginAs']);
        }


        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_list'])) {
            $list['payment_method']['list_route']['WOrderPackageController@payment_method_list'] = $list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_list'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_list']);
        }
        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_show'])) {
            $list['payment_method']['list_route']['WOrderPackageController@payment_method_show'] = $list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_show'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_show']);
        }
        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_store'])) {
            $list['payment_method']['list_route']['WOrderPackageController@payment_method_store'] = $list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_store'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_store']);
        }

        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_update'])) {
            $list['payment_method']['list_route']['WOrderPackageController@payment_method_update'] = $list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_update'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_update']);
        }
        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_default'])) {
            $list['payment_method']['list_route']['WOrderPackageController@payment_method_default'] = $list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_default'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_default']);
        }
        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_delete'])) {
            $list['payment_method']['list_route']['WOrderPackageController@payment_method_delete'] = $list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_delete'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@payment_method_delete']);
        }
        if (isset($list['VisitorPackageController']['list_route']['WVisitorPackageController@reason_for_not_visiting_lis'])) {
            $list['ReportController']['list_route']['WVisitorPackageController@reason_for_not_visiting_lis'] = $list['VisitorPackageController']['list_route']['WVisitorPackageController@reason_for_not_visiting_lis'];
            unset($list['VisitorPackageController']['list_route']['WVisitorPackageController@reason_for_not_visiting_lis']);
        }
        if (isset($list['VisitorPackageController']['list_route']['WVisitorPackageController@reason_for_not_visiting_list_export'])) {
            $list['ReportController']['list_route']['WVisitorPackageController@reason_for_not_visiting_list_export'] = $list['VisitorPackageController']['list_route']['WVisitorPackageController@reason_for_not_visiting_list_export'];
            unset($list['VisitorPackageController']['list_route']['WVisitorPackageController@reason_for_not_visiting_list_export']);
        }
        if (isset($list['VisitorPackageController']['list_route']['WVisitorPackageController@listVisited'])) {
            $list['ReportController']['list_route']['WVisitorPackageController@listVisited'] = $list['VisitorPackageController']['list_route']['WVisitorPackageController@listVisited'];
            unset($list['VisitorPackageController']['list_route']['WVisitorPackageController@listVisited']);
        }

        if (isset($list['OrderPackageController']['list_route']['WOrderPackageController@OrderAndInvoiceDifference'])) {
            $list['ReportController']['list_route']['WOrderPackageController@OrderAndInvoiceDifference'] = $list['OrderPackageController']['list_route']['WOrderPackageController@OrderAndInvoiceDifference'];
            unset($list['OrderPackageController']['list_route']['WOrderPackageController@OrderAndInvoiceDifference']);
        }
        if (isset($list['CustomerPackageController']['list_route']['WCustomerPackageController@CustomerInRoute'])) {
            $list['GisPackageController']['list_route']['WCustomerPackageController@CustomerInRoute'] = $list['CustomerPackageController']['list_route']['WCustomerPackageController@CustomerInRoute'];
            unset($list['CustomerPackageController']['list_route']['WCustomerPackageController@CustomerInRoute']);
        }

        if (isset($list['payment_method']))
            $list['payment_method']['name'] = "روش پرداخت";
        return $list;
    }




    public function blockRouteMobile()
    {
        return
            [
                "LocationController@countries", "LocationController@provinces",
                "LocationController@cities", "SettingController@list",
                "SettingController@oneSignalProxy", "ConstantController@list",
                "ConstantController@index", "ConstantController@create", "ConstantController@store",
                "ConstantController@edit", "ConstantController@update", "ConstantController@destroy",
                "LoginController@forgetRequest", "LoginController@checkForgetSmsCode", "LoginController@login", "LoginController@logout", "PriceClassController@list_all", "PriceClassController@list", "PriceClassController@store",
                "PriceClassController@update", " SearchController@index", "CompanyReportController@index", "CompanyReportController@create",
                "CompanyReportController@store", "CompanyReportController@show", "CompanyReportController@edit", "CompanyReportController@update", "CompanyReportController@destroy", "FavoriteController@index", "FavoriteController@add", "FavoriteController@delete",
                "CompanyFavoriteController@index", "CompanyFavoriteController@add", "CompanyFavoriteController@delete", "CommentController@store",
                "CommentController@list", "CommentController@rate_store", "CommentController@rate_list", "SlideController@index", "NewsController@index",
                "NewsController@show", "NewsController@top", "FileController@store",
                "MessageController@store", "MessageController@store",
                "SuggestionController@index", "SuggestionController@store", "CouponController@check", "PaymentMethodController@index",
                "PaymentController@pay", "PeriodicOrderController@index", "PeriodicOrderController@create", "PeriodicOrderController@store",
                "PeriodicOrderController@show", "PeriodicOrderController@edit", "PeriodicOrderController@update", "PeriodicOrderController@destroy",
                "CategoryController@tree", "CategoryController@products", "CategoryController@index", "CategoryController@create",
                "CategoryController@store", "CategoryController@show", "CategoryController@edit", "CategoryController@update",
                "CategoryController@destroy", "RouteController@CustomerRegisterByVisitor",
                "PositionController@index", "PositionController@store", "PositionController@show", "ReasonForNotVisitingController@store",
                "VisitorController@setTimeVisit", "VisitorController@getTime", "VisitorController@ListNotVisited",
                "VisitorController@getOrderRegisterByVisitor", "VisitorController@VisitorIsHaveOrderForUser", "PhotoController@index",
                "PhotoController@create", "PhotoController@store", "PhotoController@show", "PhotoController@edit", "PhotoController@update",
                "PhotoController@destroy", "NotificationController@index", "NotificationController@create", "NotificationController@store",
                "NotificationController@show", "NotificationController@edit", "NotificationController@update", "NotificationController@destroy","ConstantController@show"
            ];
    }
}
