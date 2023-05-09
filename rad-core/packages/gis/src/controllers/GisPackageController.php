<?php

namespace core\Packages\gis\src\controllers;

use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Illuminate\Http\Request;
use Core\Packages\gis\Points;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Core\Packages\gis\Country;
use Core\Packages\gis\Province;
use App\ModelFilters\AreaFilter;
use Core\Packages\gis\UserRoute;
use App\ModelFilters\RouteFilter;
use Core\Packages\gis\DeliveryRoute;
use Illuminate\Support\Facades\Hash;
use Core\Packages\gis\DeliveryRouteUser;
use Core\System\Exceptions\CoreException;
use Core\System\Exceptions\CoreExceptionOk;
use Core\Packages\gis\src\request\AreaRequest;
use Core\Packages\gis\src\request\PointRequest;
use Core\Packages\gis\src\request\RouteRequest;
use Core\System\Http\Controllers\CoreController;


class GisPackageController extends CoreController
{

    private $_route_fillable = [
        'province_id',
        "visitors",
        "start_at",
        "end_at",
        'city_id',
        'area_id',
        'route',
        'customer_ids',
    ];


    private $_area_fillable = [
        'province_id',
        'city_id',
        'area',
    ];
    private $_point_fillable = [
        'route_id',
        'user_id',
        'lan',
        'lat',
        'state',
    ];

    public function countries()
    {
        /** @var Country[] $countries */
        $countries = Country::get();

        $data = [];
        foreach ($countries as $country) {
            $data[] = [
                'id' => $country->id,
                'name' => $country->name,
            ];
        }

        return $data;
    }






    public function delivery_index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        $del = DeliveryRoute::with('Areas');
       $del->withcount('users');

        if (auth('api')->user()['kind'] == "company")
            $del->where('company_id', auth('api')->user()['company_id']);


        if ($request->area_id) {
            $area = $request->area_id;
            $del->whereHas('Areas', function ($q) use ($area) {
                return $q->where('id', $area);
            });
        }
        if ($request->city_id) {
            $city_id = $request->city_id;
            $del->whereHas('Areas.city', function ($q) use ($city_id) {
                return $q->where('id', $city_id);
            });

        }


        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $del = $del->filter($request->all())->get();
        } else {
            $del = $del->filter($request->all())->jsonPaginate($limit);
        }

        return $del;
    }



    public function delivery_show($id)
    {
        $result = DeliveryRoute::with('Areas', 'Users')->find($id);
        return $result;
    }
    public function Customer_inroute(Request $request , $sort = "id" , $order = "desc" , $limit = 10)
    {
        // jsonPaginate($limit)
       $customers = DeliveryRouteUser::select('user_id')->whereIn('route_id' , $request->ids);
return  Users::with('provinces','cities','Areas',"Products","Orders","Routes","logLogin","PriceClasses",'addresses','IntroducerCode','PaymentMethodCustomer', 'categories','CustomerGrade','CustomerGroup', 'IntroductionSource')->whereIn('id' , $customers)->orderby('created_at', 'desc')->jsonPaginate($limit);
    }

    public function delivery_store(Request $request)
    {
        if($request->route_id and $request->customers_id){

            foreach ($request->customers_id as $customer) {

                $dru = new DeliveryRouteUser();

                // dd($dru->where('user_id', $customer)->where('route_id' , $request->route_id)->count() == 0);
                if ( $dru->where('user_id', $customer)->where('route_id' , $request->route_id)->count() ==  0) {

                    $dru->route_id = $request->route_id;
                    $dru->user_id = $customer;
                    $dru->save();

                }




            }
                throw new CoreExceptionOk('مشتری ها با موفقیت اضافه شدند');
        }



        $payload = $request->only($this->_route_fillable);
        $stor = DeliveryRoute::create([
            "area_id" => $request->area_id,
            "route" => $request->route,
            "company_id" => auth('api')->user()['company_id'],

        ]);

        // $stor->users()->sync($companys->pluck('id'));

        if($request != null){
              $oldroutes = UserRoute::whereIn('route_id', $request->oldroute_id)->get();


        foreach ($oldroutes as $oldroute) {

            $dru = new DeliveryRouteUser();
            $dru->route_id = $stor->id;
            $dru->user_id = $oldroute->user_id;
            $dru->save();
        }
        }

        if ($request->customers_id) {

            foreach ($request->customers_id as $customer) {

                $dru = new DeliveryRouteUser();


                if ($dru->where('user_id', $customer)->where('route_id' , $request->route_id)->count() == 0) {
                    $dru->rout_id = $stor->id;
                    $dru->user_id = $customer;
                    $dru->save();
                }


            }

        }






        return [
            'status' => true,
            'message' => trans('با موفقیت ثبت شد'),
        ];
    }

    public function delivery_update(Request $request, $id)
    {

        $stor = DeliveryRoute::find($id);
         $stor->area_id = $request->area_id;
         $stor->route = $request->route;
         $stor->save();

        if($request != null){
            $oldroutes = UserRoute::whereIn('route_id', $request->oldroute_id)->get();


      foreach ($oldroutes as $oldroute) {

          $dru = new DeliveryRouteUser();
          $dru->route_id = $id;
          $dru->user_id = $oldroute->user_id;
          $dru->save();
      }
      }

      if ($request->customers_id) {

          foreach ($request->customers_id as $customer) {

              $dru = new DeliveryRouteUser();


              if ($dru->where('user_id', $customer)->where('route_id' , $id)->count() == 0) {
                  $dru->rout_id = $id;
                  $dru->user_id = $customer;
                  $dru->save();
              }


          }

      }
     throw new  CoreExceptionOk('مسیر با موفقیت بروزرسانی شد');
    }

    public function add_customer_at_route(Request $request) {


     return  Users::wherehas('Areas' , function($query) use ($request){
        return $query->where('areas.id' , $request->area_id);
     })->get();

    }



    public function delivery_destroy(Request $request , DeliveryRoute $rou)
    {



        if($request->has('customer_id')){

                 DeliveryRouteUser::whereIn('user_id' , $request->customer_id)->where('route_id' , $request->route_id)->delete();
            throw new CoreExceptionOk('با موفقیت حذف شد');


        }

   $validated = $request->validate([
            'customer_id' => 'max:255|array',
            'ids' => 'required|array',
        ]);

        $routes = DeliveryRoute::whereIn('id', array_unique($validated['ids']));

        if ($routes->count()) {
            if (count(array_unique($request->ids)) != $routes->count()) {

                return [
                    'status' => true,
                    'message' => "شناسه " . implode(" , ", array_diff(array_unique($request->ids), $routes->pluck('id')->toArray())) . " یافت نشد"
                ];
            }
        } else {


            return [
                'status' => true,
                'message' => "شناسه " . implode(" , ", $request->ids) . " یافت نشد"
            ];
        }

        $rou->secureDelete($validated['ids'] , ['tours']);
    }
    public function areas_index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        $companyId = auth('api')->user()->company_id;
        $areas = Areas::with('users', 'city', 'province', 'province.country')
            ->filter($request->all(), AreaFilter::class);

            if (!(auth('api')->user()->kind == 'superAdmin' || auth('api')->user()->kind == 'admin')) {
            $areas = $areas->whereHas('users', function ($query) use ($companyId) {
                $query->where('user_id', $companyId);
            });
        }

        $areas = $areas->orderBy($sort, $order)->jsonPaginate($limit);

        return $areas;
    }

    public function routes_index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {


        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }
        $companyId = auth('api')->user()->company_id;

        $routes = Routes::with(['users' => function ($q) {
            return $q->where('kind', 'customer');
        }, 'area'])->filter($request->all(), RouteFilter::class);

        if (auth('api')->user()->kind != 'superAdmin' && auth('api')->user()->kind != 'admin') {
            $routes = $routes->whereHas('users', function ($query) use ($companyId) {
                $query->where('user_id', $companyId);
            });
        }







        return $routes->jsonPaginate($limit);
    }

    public function provinces(Request $request)
    {
        $countriesId = $request->get('countries');
        $company_id = null;
        if ($request->company_id) {
            $company_id = $request->company_id;
        } else {
            if (auth('api')->user()->kind == 'company') {
                $company_id = auth('api')->user()->company_id;
            }
        }
        if ($company_id) {
            $users = new Users();
            $company_provinces_gis = $users->whereHas('provinces')->with('provinces')->where("id", $company_id)->get()->pluck("provinces.*.id")->collapse();
            $provinces = Country::with(['provinces' => function ($query) use ($company_provinces_gis) {
                $query->whereIn('id', $company_provinces_gis);
            }]);
            if ($countriesId and !in_array(0, $countriesId)) {
                $provinces = $provinces->whereIn('id', $countriesId);
            }

            return $provinces->get();
        }
        /** @var Province[] $provinces */

        $provinces = Country::whereHas('provinces')->with('provinces');
        if ($countriesId and !in_array(0, $countriesId)) {
            $provinces = $provinces->whereIn('id', $countriesId);
        }
        return $provinces->get();
    }

    public function cities(Request $request)
    {


        if (is_array($request->provinces)) {
            $provincesId = $request->provinces;
        } else {
            $provincesId = [$request->provinces];
        }
        $company_id = null;
        if ($request->company_id) {
            $company_id = $request->company_id;
        } else {
            if (auth('api')->user()->kind == 'company') {
                $company_id = auth('api')->user()->company_id;
            }
        }
        if ($company_id) {
            $users = new Users();
            $company_gis = $users->whereHas('cities')->with('cities')->where("id", $company_id)->get()->pluck("cities.*.id")->collapse();

            $cities = Province::with('cities')


            ;
            if ($provincesId and !in_array(0, $provincesId)) {
                $cities = $cities->whereIn('id', $provincesId);
            }
            return $cities->get();
        }

        /** @var City[] $cities */
        $cities = Province::whereHas('cities')->with('cities');
        if ($provincesId and !in_array(0, $provincesId)) {
            $cities = $cities->whereIn('id', $provincesId);
        }

        return $cities->get();
    }


    public function areas_list_orginal(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {


        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }
        $company_id = auth('api')->user()->company_id;
        if ($request->company_id) {
            $company_id = $request->company_id;
        }
        if ($company_id) {
            $cities_id = $request->cities_id;
            $users = new Users();
            $company_gis = $users->whereHas('areas')->with('areas')->where("id", $company_id)->get()->pluck("areas.*.id")->collapse();
            $areas = City::with(['areas' => function ($query) use ($company_gis) {
                $query->whereIn('id', $company_gis);
            }]);
            if ($cities_id and !in_array(0, $cities_id)) {
                $areas = $areas->whereIn('id', $cities_id);
            }
            return $areas->get();
        }
        $areas = Areas::with('city', 'province', 'province.country')->filter($request->all(), AreaFilter::class)->orderBy($sort, $order)->jsonPaginate($limit);

        return $areas;
    }

    public function areas_list(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }
        $company_id = auth('api')->user()->company_id;
        if ($request->company_id) {
            $company_id = $request->company_id;
        }

        $users = new Users();
        $cities_id = $request->cities_id;

        if ($company_id) {
            $company_gis = $users->whereHas('areas')->with('areas')->where("id", $company_id)->get()->pluck("areas.*.id")->collapse();
            $areas = City::with(['areas' => function ($query) use ($company_gis) {
                $query->whereIn('id', $company_gis);
            }]);
            if ($cities_id and !in_array(0, $cities_id)) {
                $areas = $areas->whereIn('id', $cities_id);
            }
            return $areas->get();
        }
        $company_gis = $users->whereHas('areas')->with('areas')->get()->pluck("areas.*.id")->collapse();
        $areas = City::with(['areas' => function ($query) use ($company_gis) {
            $query->whereIn('id', $company_gis);
        }]);

        if ($cities_id and !in_array(0, $cities_id)) {
            $areas = $areas->whereIn('id', $cities_id);
        }
        return $areas->get();
    }

    public function areas_show($id)
    {
        $result = Areas::with('city', 'province', 'province.country')->find($id);
        return $result;
    }

    public function areas_store(AreaRequest $request)
    {
        $company_id = auth('api')->user()->company_id;
        $payload = $request->only($this->_area_fillable);
        $result = Areas::_()->store($payload, $company_id);
        return [
            'status' => true,
            'message' => trans('messages.gis.area.store'),
        ];
    }

    public function areas_update(AreaRequest $request, $id)
    {
        $payload = $request->only($this->_area_fillable);
        $result = Areas::_()->updateU($payload, $id);
        return [
            'status' => true,
            'message' => trans('messages.gis.area.update'),
        ];
    }

    public function points_list(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        $company_id = auth('api')->user()->company_id;
        if ($request->company_id) {
            $company_id = $request->company_id;
        }

        if ($company_id) {

            $routes_id = $request->routes_id;
            $users = new Users();
            $company_gis = $users->whereHas('points')->with('points')->where("id", $company_id)->get()->pluck("points.*.id")->collapse();

            $areas = Points::with(['user' => function ($query) use ($company_gis) {
                $query->whereIn('id', $company_gis);
            }]);
            if ($routes_id and !in_array(0, $routes_id)) {
                $areas = $areas->whereIn('route_id', $routes_id);
            }
            return $areas->get();
        }
        $result = Points::_()->list();
        return $this->responseHandler($result);
    }

    public function points_show($id)
    {
        $result = Points::_()->list($id);
        return $this->responseHandler($result);
    }

    public function points_store(PointRequest $request)
    {
        $payload = $request->only($this->_point_fillable);
        $result = Points::_()->store($payload);
        return [
            'status' => true,
            'message' => trans('messages.gis.point.store'),
        ];
    }

    public function points_update(PointRequest $request, $id)
    {
        $payload = $request->only($this->_point_fillable);
        $result = Points::_()->updateU($payload, $id);
        return [
            'status' => true,
            'message' => trans('messages.gis.point.update'),
        ];
    }

    public function routes_list(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }
        $company_id = auth('api')->user()->company_id;
        if ($request->company_id) {

            $company_id = $request->company_id;
        }
        if ($company_id) {
            $areas_id = $request->areas_id;
            $users = new Users();
            $company_gis = $users->whereHas('routes')->with('routes')->where("id", $company_id)->get()->pluck("routes.*.id")->collapse();
            $areas = Areas::with(['routes.visitors.user', 'routes' => function ($query) use ($company_gis) {
                $query->whereIn('id', $company_gis);
            }]);
            if ($areas_id and !in_array(0, $areas_id)) {
                $areas = $areas->whereIn('id', $areas_id);
            }
            return $areas->get();
        } else {
            $areas_id = $request->areas_id;
            $users = new Users();
            $company_gis = $users->whereHas('routes')->with('routes')->get()->pluck("routes.*.id")->collapse();
            $areas = Areas::with(['routes.visitors.user', 'routes' => function ($query) use ($company_gis) {
                $query->whereIn('id', $company_gis);
            }]);
            if ($areas_id and !in_array(0, $areas_id)) {
                $areas = $areas->whereIn('id', $areas_id);
            }
            return $areas->get();
        }
    }

    public function routes_show($id)
    {
        $result = Routes::with('area', 'visitors.user')->find($id);
        return $result;
    }

    public function routes_store(RouteRequest $request)
    {
        $payload = $request->only($this->_route_fillable);
        $result = Routes::_()->store($payload);
        return [
            'status' => true,
            'message' => trans('messages.gis.route.store'),
        ];
    }

    public function routes_update(RouteRequest $request, $id)
    {
        $payload = $request->only($this->_route_fillable);
        $result = Routes::_()->updateU($payload, $id);
        return [
            'status' => true,
            'message' => trans('messages.gis.route.update'),
        ];
    }

    public function routes_destroy(Request $request , Routes $rot)
    {
        if (!is_array($request->route_ids) || !count($request->route_ids)) {
            return [
                'status' => true,
                'message' => 'شناسه مسیر ها باید به صورت آرایه باشد',
            ];
        }

        $routes = Routes::whereIn('id', array_unique($request->route_ids));
        if ($routes->count()) {
            if (count(array_unique($request->route_ids)) != $routes->count()) {

                return [
                    'statue' => true,
                    'message' => "شناسه " . implode(" , ", array_diff(array_unique($request->route_ids), $routes->pluck('id')->toArray())) . " یافت نشد"
                ];
            }
        } else {


            return [
                'statue' => true,
                'message' => "شناسه " . implode(" , ", $request->route_ids) . " یافت نشد"
            ];
        }

        $rot->secureDelete($request->route_ids , ['Customers' ,'Tour' ]);

    }

    public function areas_destroy(Request $request , Areas $areass)
    {
        //$result = Areas::_()->destroyRecord($request->id);

        // if ($this->ISCompany()) {
            // $order->where('company_id', $this->ISCompany());
        // }

        // $area = Areas::where('id', $request->id)

        //     ->delete();
        // return [
        //     'status' => true,
        //     'message' => trans('messages.gis.area.destroy'),
        // ];
        $areass->secureDelete($request->id , ['Routes']);

    }

    public function points_destroy(Request $request)
    {
        $result = Points::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.gis.point.destroy'),
        ];
    }
    private function ISCompany()
    {
        if (auth('api')->user()['kind'] == 'admin')
            return 0;
        else
            return auth('api')->user()->company_id;
    }
}
