<?php

namespace core\Packages\customer\src\controllers;


use File;
use App\User;
use Response;
use Carbon\Carbon;
use Core\Packages\gis\City;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Core\Packages\order\Order;
use Hekmatinasser\Verta\Verta;
use Core\Packages\gis\Province;
use Core\Packages\user\Address;
use Core\Packages\user\Contact;
use Core\System\Helper\CrmSabz;
use Core\Packages\role\UserRoles;
use Core\Packages\common\Constant;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Order\PaymentMethod;
use Core\Packages\visitor\Visitors;
use Illuminate\Support\Facades\Log;
use App\ModelFilters\ConstantFilter;
use App\ModelFilters\CustomerFilter;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Core\System\Export\UsersExportExcel;
use Core\System\Exceptions\CoreException;
use Core\Packages\customer\CompanyCustomer;
use Symfony\Component\VarDumper\Cloner\Data;
use Core\Packages\order\PaymentMethodCustomer;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\customer\src\request\StoreRequest;
use Core\Packages\customer\src\request\UpdateRequest;
use Core\Packages\customer\src\request\DestroyRequest;
use Core\Packages\customer\src\request\ApproveStateRequest;
use Core\Packages\customer\src\request\AddCustomerToRouterRequest;
use Core\Packages\product\Product;
use Core\Packages\robots\LinkCustomer;

class CustomerPackageController extends CoreController
{

    function geoJson($customers)
    {
        $original_data = json_decode($customers, true);
        $features = [];
        $lat = 0;
        $long = 0;
        foreach ($original_data as $value) {
            if (!empty($value['addresses'])) {
                foreach ($value['addresses'] as $address) {
                    $lat = $address['lat'];
                    $long = $address['long'];
                }
            }
            $features[] = [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$long, $lat]],
                'properties' => $value,
            ];
        };
        $allfeatures = ['type' => 'FeatureCollection', 'features' => $features];
        return json_encode($allfeatures, JSON_PRETTY_PRINT);
    }

    public function index(Request $request, $order = "desc", $limit = 10)
    {



        if (auth('api')->user()->show_users_list == 0) {
            throw new CoreException('دسترسی به مشتریان برای شرکت مورد نظر غیرفعال می باشد');
        }
        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }

        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'provinces',
                'cities',
                'Areas',
                "Products",
                "Orders",
                "Routes",
                "logLogin",
                "PriceClasses",
                'addresses',
                'IntroducerCode',
                'PaymentMethodCustomer',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                }
            ]);


        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $customer = $customer->filter($request->all());
        } else {
            $customer = $customer->whereCities($cities)->filter($request->all());
        }

        if (request('from_date') && request('to_date')) {
            $customer = $customer->where('created_at', '>', request('from_date'))
                ->where('created_at', '<=', request('to_date'));
        }

        // if (request('cities') && count(request('cities'))) {
        //     $iCities = array_map('intval', request('cities'));
        //     $customer = $customer->whereCities($iCities);
        // }

        // if (request('areas') && count(request('areas'))) {
        //     $iAreas = request('areas');
        //     $customer = $customer->whereHas('areas', function ($query) use ($iAreas) {
        //         $query->where('id', $iAreas);
        //     });
        // }

        // if (request('routes') && count(request('routes'))) {
        //     $iRoutes = request('routes');
        //     $customer = $customer->whereHas('routes', function ($query) use ($iRoutes) {
        //         $query->whereIn('id', $iRoutes);
        //     });
        // }

        // if (request('users') && count(request('users'))) {
        //     $customer = $customer->whereIn('id', request('users'));
        // }

        // if (request('brands') && count(request('brands'))) {
        //     $iBrands = request('brands');
        //     $customer = $customer->whereHas('products', function ($query) use ($iBrands) {
        //         $query->whereIn('brand_id', $iBrands);
        //     });
        // }

        // if (request('products') && count(request('products'))) {
        //     $iProducts = request('products');
        //     $customer = $customer->whereHas('products', function ($query) use ($iProducts) {
        //         $query->whereIn('id', $iProducts);
        //     });
        // }

        // if (request('product_groups') && count(request('product_groups'))) {
        //     $iProductGroups = request('product_groups');
        //     $customer = $customer->whereHas('products', function ($query) use ($iProductGroups) {
        //         $query->whereIn('category_id', $iProductGroups);
        //     });
        // }

        // if (request('categories') && count(request('categories'))) {
        //     $iCategories = request('categories');
        //     $customer = $customer->whereHas('categories', function ($query) use ($iCategories) {
        //         $query->whereIn('id', $iCategories);
        //     });
        // }

        // if (request('grades')) {
        //     $customer = $customer->whereIn('customer_grade', request('grades'));
        // }

        // if (request('active')) {
        //     if (request('active') == true) {
        //         $customer = $customer->where('status', 'active');
        //     }

        //     if (request('active') == false) {
        //         $customer = $customer->where('status', 'inactive');
        //     }
        // }

        // if (request('order_register_source')) {
        //     $orderRegisterSource = request('order_register_source');
        //     $customer = $customer->whereHas('orders', function ($query) use ($orderRegisterSource) {
        //         $query->where('registered_source', $orderRegisterSource);
        //     });
        // }

        // if (request('categories') && count(request('categories'))) {
        //     $iCategories = request('categories');
        //     $customer = $customer->whereHas('categories', function ($query) use ($iCategories) {
        //         $query->whereIn('id', $iCategories);
        //     });
        // }


        // if (request('reason_for_not_visiting')) {
        //     if (request('reason_for_not_visiting') == true) {
        //         $customer = $customer->whereHas('ReasonForNotVisitings')->whereDoesntHave('orders');
        //     }

        //     if (request('reason_for_not_visiting') == false) {
        //         $customer = $customer->whereDoesntHave('ReasonForNotVisitings')->whereHas('orders');
        //     }
        // }

        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            ini_set('memory_limit', '512M');
            $customer = $customer->get();
        }

        if (request()->has('geo') && request()->get('geo') == 'true') {
            ini_set('memory_limit', '512M');
            $customer = $customer->get();

            $customerIds = [];
            foreach ($customer->toArray() as $cu) {
                $customerIds[] = $cu['id'];
            }

            $orders = Order::select('customer_id', 'created_at')
                ->join(DB::raw('(Select max(id) as id from orders group by customer_id) LatestMessage'), function ($join) {
                    $join->on('orders.id', '=', 'LatestMessage.id');
                })->whereIN('customer_id', $customerIds)->get()->toArray();

            $customerOrders = [];
            foreach ($orders as $order) {
                $customerOrders[$order['customer_id']] = $order['created_at'];
            }

            foreach ($customer as $index => $cu) {
                if (isset($customerOrders[$cu['id']])) {
                    $v = new Verta($customerOrders[$cu['id']]);
                    $customer[$index]['order_last_date'] = str_replace('-', '/', $v->formatDate());
                }

                if (!empty($cu['categories'][0]['constant_fa'])) {
                    $customer[$index]['category_name'] = $cu['categories'][0]['constant_fa'];
                }

                $customer[$index]['address'] = (isset($cu['addresses'][0]['address'])) ? $cu['addresses'][0]['address'] : "null";
            }

            $jsongFile = time() . '.geojson';
            File::put(base_path().'/public_html/upload/json/' . $jsongFile, $this->geoJson($customer));
            return [
                'status' => true,
                'url' => url('/') . '/upload/json/'  . $jsongFile
            ];
        } else {

            $size = (isset($request->page['size'])) ? $request->page['size'] : 10;
            return $customer = $customer->filter($request->all(), CustomerFilter::class)->jsonPaginate($size);
        }

        return $customer;
    }




    public function sign(Request $request)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        if (auth('api')->user()->show_users_list == 0) {
            throw new CoreException('دسترسی به مشتریان برای شرکت مورد نظر غیرفعال می باشد');
        }

        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }
        $customer = Users::with(['Photo' => function ($q) {
            $q->select('*');
        }])
            ->select('id', 'store_name', 'created_at', 'first_name', 'last_name', 'mobile_number', 'photo_id')
            ->where('users.kind', Users::KIND_CUSTOMER)
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->where('approve', '0');


        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $customer = $customer->filter($request->all(), CustomerFilter::class);
        } else {
            $customer = $customer->whereCities($cities)->filter($request->all(), CustomerFilter::class);
        }





        ini_set('memory_limit', '512M');
        $customer = $customer->orderBy('created_at', 'DESC')->get();

        return $customer;
    }

    public function CustomerInRoute(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        if (auth('api')->user()->company_id) {
            $user = Users::find(auth('api')->user()->company_id);
            $hasRoute = $user->Routes()->where('route_id', $request->routes_id)->exists();

            if (!isset($hasRoute)) {
                throw new CoreException(' شناسه ' . $request->routes_id . ' یافت نشد');
            }
        }

        $routers_id = array();
        if ($request->has('routes_id')) {
            $routers_id = $request->get('routes_id');
        }
        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }
        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'provinces',
                'cities',
                'Areas',
                "Routes",
                "PriceClasses",
                'addresses',
                'IntroducerCode',
                'PaymentMethodCustomer',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                }
            ]);


        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $customer = $customer->filter($request->all(), CustomerFilter::class)->orderBy($sort, $order);
        } else {
            $customer = $customer->whereCities($cities)->filter($request->all(), CustomerFilter::class)->orderBy($sort, $order);
        }



        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            ini_set('memory_limit', '512M');
            $customer = $customer->get();
        } else {
            $customer = $customer->jsonPaginate($limit);
        }

        return $customer;
    }


    public function CustomerInRouteDelete(AddCustomerToRouterRequest $request)
    {
        if (auth('api')->user()->company_id) {
            $user = Users::find(auth('api')->user()->company_id);
            $hasRoute = $user->Routes()->where('route_id', $request->routes_id)->exists();

            if (!isset($hasRoute)) {
                throw new CoreException(' شناسه ' . $request->routes_id . ' یافت نشد');
            }
        }
        $routerIdAdd = $request->routes_id;
        $customer_id = $request->customer_id;
        $customers = Users::where('users.kind', Users::KIND_CUSTOMER)->whereIn('users.id', $customer_id)
            ->select('users.*')->with(['Routes']);

        $customers = $customers->get();

        $is_deleted = false;
        foreach ($customers as $customer) {
            $is_deleted = $customer->Routes()->detach($routerIdAdd);
        }

        if ($is_deleted) {
            return [
                'status' => true,
                'message' => "مشتری از مسیر حذف شد",
                'id' => "",
            ];
        } else {
            return [
                'status' => false,
                'message' => "مشتری از مسیر حذف نشد",
                'id' => "",
            ];
        }
    }


    public function CustomerInRouteAdd(AddCustomerToRouterRequest $request)
    {

        $routerIdAdd = $request->routes_id;
        $customer_id = $request->customer_id;

        if (auth('api')->user()->company_id) {

            $user = Users::find(auth('api')->user()->company_id);
            $hasRoute = $user->Routes()->where('route_id', $request->routes_id)->exists();

            if (!isset($hasRoute)) {
                throw new CoreException(' شناسه ' . $request->routes_id . ' یافت نشد');
            }
        }

        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }


        $customers = Users::where('users.kind', Users::KIND_CUSTOMER)->whereIn('users.id', $customer_id)
            ->select('users.*')->with(['Routes'])->get();


        $routee = Routes::find($routerIdAdd);
        $is_add = false;
        foreach ($customers as $customer) {


            $customer->Routes()->where('route_id', $request->routes_id)->detach();
            $is_add = $customer->Routes()->toggle($routee);
            $is_add = true;
        }

        if ($is_add) {
            return [
                'status' => true,
                "message" => "مشتری به مسیر اضافه شد",
                'id' => "",
            ];
        } else {
            return [
                'status' => false,
                "message" => "مشتری به مسیر اضافه نشد",
                'id' => "",
            ];
        }
    }

    public function export(Request $request, $sort = "id", $order = "desc")
    {
        ini_set('memory_limit', '512M');

        if ($request->ids == "false") {
            $ids = array();
        } else {
            $ids = explode(",", $request->ids);
        }        // return Excel::download(new UsersExportExcel($request), 'users.xlsx');

        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }
        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'Provinces',
                'Cities',
                'Areas',
                'Routes',
                'UserRole',
                "PriceClasses",
                'Addresses',
                'IntroducerCode',
                'PaymentMethodCustomer',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                }
            ]);

        if (count($ids) > 0) {
            $customer = $customer->whereIn('id', $ids);
        }

        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $customer = $customer->filter($request->all(), CustomerFilter::class)->orderBy($sort, $order);
        } else {
            $customer = $customer->whereCities($cities)->filter($request->all(), CustomerFilter::class)->orderBy($sort, $order);
        }


        $results['data'] = array();
        $customers = $customer->get()->toArray();
        //set tiltle for excel


        //add product to list
        foreach ($customers as $custom) {


            $provinces = array();
            if ($custom['provinces']) {
                foreach ($custom['provinces'] as $province) {
                    array_push($provinces, $province['name']);
                }
            }
            $citys = array();
            if ($custom['cities']) {
                foreach ($custom['cities'] as $city) {
                    array_push($citys, $city['name']);
                }
            }
            $areas = array();
            if ($custom['areas']) {
                foreach ($custom['areas'] as $area) {
                    array_push($areas, $area['area']);
                }
            }

            $users_routes = array();
            if ($custom['routes']) {
                foreach ($custom['routes'] as $user_route) {
                    array_push($users_routes, $user_route['route']);
                }
            }
            $users_Roles = array();
            if ($custom['user_role']) {
                foreach ($custom['user_role'] as $user_role) {
                    array_push($users_Roles, (isset($user_role['name'])) ? $user_role['name'] : '');
                }
            }
            $categories = array();
            if ($custom['categories']) {
                foreach ($custom['categories'] as $categorie) {
                    array_push($categories, $categorie['constant']);
                }
            }


            $date_create = new Verta($custom['created_at']);

            $results['data'][] = [
                "شناسه" => $custom['id'],
                "نام" => $custom['full_name'],
                "عرض جغرافیایی" => (isset($custom['addresses'][0]['lat'])) ? $custom['addresses'][0]['lat'] : null,
                "طول جغرافیایی"  => (isset($custom['addresses'][0]['long'])) ? $custom['addresses'][0]['long'] : null,
                "آدرس"  => (isset($custom['addresses'][0]['address'])) ? $custom['addresses'][0]['address'] : null,
                "شماره ی مشتری"  => $custom['referral_id'],
                "شماره ی ثابت"  => $custom['phone_number'],
                "موبایل" => $custom['mobile_number'],
                "نام فروشگاه" => $custom['store_name'],
                "استان" => implode(',', $provinces),
                "شهر" => implode(',', $citys),
                "نام منطقه" => implode(',', $areas),
                "نام مسیر" => implode(',', $users_routes),
                "کد معرف (سازنده)" => $custom['introducer_code_id'],
                "منبع ورودی" => (isset($custom['introduction_source']['constant_fa'])) ? $custom['introduction_source']['constant_fa'] : "",
                "توضیحات" => $custom['description'],
                "صنف" => implode(',', $categories),
                "رتبه مشتری" => (isset($custom['customer_grade']['constant_fa'])) ? $custom['customer_grade']['constant_fa'] : "",
                "فعالیت تخصصی" => (isset($custom['customer_group']['constant_fa'])) ? $custom['customer_group']['constant_fa'] : "",
                "وضعیت" => $custom['status_translate'],
                "تصویب" => ($custom['approve'] == 1) ? "بله" : "خیر",
                "دسته بندی مشتری" => implode(',', $users_Roles),
                "امتیاز" => $custom['score'],
                "تاریخ ایجاد" => str_replace('-', '/', $date_create->formatDate())
            ];
        }


        // return json_encode($results);
        return $results;
        //sahram

        /*   $url = "http://retailapi.kheilisabz.com";
        $username = "admin";
        $password = "123";
        $url = $url . "/api/accounts";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $username . ":" . $password,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $behzad = json_decode($response, true);
        $behzad = $behzad['content'];
        $alld = array();
        $count = 0;
        $ncoint = 0;
        foreach ($behzad as $b) {


                if(isset($b['mobile']))
         {
           /* $user= Users::where('referral_id',$b['id'])->first();
            if($user)
            {
            /*    $count++;
         Log::info("*************************************************************************************");
            Log::info($count."---" .$b['id']."  ".$b['name']." " .$b['mobile'] ."موجود است");
            Log::info($user->id."  ".$user->first_name ." ".$user->last_name." "  ."موجود است");
            Log::info("*************************************************************************************");

            }
            else
            {
                $ncoint++;
                Log::info("----------------------------------------------------------------------------------------");
                Log::info($ncoint."---" .$b['id'] ." ///// ".$b['retailAccountGrade']."=>".array_key_exists($b['retailAccountGrade'],$this->crmToMasterCustomerGrade()).
                "\n ///// ".$b['activityCategory']."=>".array_key_exists($b['activityCategory'],$this->crmToMasterActivityCategory()).
                "\n ///// ".$b['inputSource']."=>".array_key_exists($b['inputSource'],$this->crmToMasterInputSource()).
                "\n ///// ".$b['accountType']."=>".array_key_exists($b['accountType'],$this->crmToMasterActivityCategory()).
                "\n ///// ".$b['addresses'][0]['province']['city']['id']."=>".array_key_exists($b['addresses'][0]['province']['city']['id'],$this->crmToMasterCities()).
                "\n ///// ".$b['addresses'][0]['province']['id']."=>".array_key_exists($b['addresses'][0]['province']['id'],$this->crmToMasterProvinces()));
                Log::info("--------------------------------------------------------------------------------------");
            $date_create = new Verta($b['modifiedOn']);
            $alld['data'][] = [
                "شناسه" => $b['id'],
              //  "نام" => $b['name'],
             //   "تلفن" => $b['phone'],
             //   "موبایل" => $b['mobile'],
             //   "specializedFieldOFactivity" => $b['phone'],
             //   "ایمیل" => $b['email'],
             //   "retailAccountGrade" => $b['retailAccountGrade'],
             //   "retailAccountType" => $b['retailAccountType'],
             //   "retailNationalID" => $b['retailNationalID'],
             //   "inputSource" => $b['inputSource'],
                "code" => $b['code'],
             //   "activityCategory" => $b['activityCategory'],
             //   "accountType" => $b['accountType'],
             //   "modifiedOn" =>  str_replace('-', '/', $date_create->formatDate()),
                "ادرس" => $b['addresses'][0]['postalAddress'],
             //   "شناسه آدرس " => $b['addresses'][0]['id'],
            //    "شناسه ی استان " =>  (isset($b['addresses'][0]['province']['id'])) ? $b['addresses'][0]['province']['id']:"",
            //    "شناسه ی شهر " => (isset($b['addresses'][0]['province']['city']['id'])) ? $b['addresses'][0]['province']['city']['id']:"",
            //    "نام استان" =>  (isset($b['addresses'][0]['province']['name'])) ? $b['addresses'][0]['province']['name']:"",
             //   "نام شهر" => (isset($b['addresses'][0]['province']['city']['name'])) ?$b['addresses'][0]['province']['city']['name']:""

            ];

            }


         }
         //   else
           // Log::info($b['id']."  ".$b['name']." " .$b['mobile'] ."موجود است");


            $date_create = new Verta($b['modifiedOn']);
            $alld['data'][] = [
                "شناسه" => $b['id'],
                //   "نام" => $b['name'],
                //   "تلفن" => $b['phone'],
                //    "موبایل" => $b['mobile'],
                //    "specializedFieldOFactivity" => $b['phone'],
                //    "ایمیل" => $b['email'],
                //    "retailAccountGrade" => $b['retailAccountGrade'],
                // "retailNationalID" => $b['retailNationalID'],
                //"retailAccountType" => $b['retailAccountType'],
                //    "inputSource" => $b['inputSource'],
                //     "activityCategory" => $b['activityCategory'],
                //    "code" => $b['code'],
                //    "accountType" => $b['accountType'],
                //    "modifiedOn" =>  str_replace('-', '/', $date_create->formatDate()),
                "ادرس" => $b['addresses'][0]['postalAddress'],
                //  "شناسه آدرس " => $b['addresses'][0]['id'],
                //     "شناسه ی استان " =>  (isset($b['addresses'][0]['province']['id'])) ? $b['addresses'][0]['province']['id']:"",
                //    "شناسه ی شهر " => (isset($b['addresses'][0]['province']['city']['id'])) ? $b['addresses'][0]['province']['city']['id']:"",
                //    "نام استان" =>  (isset($b['addresses'][0]['province']['name'])) ? $b['addresses'][0]['province']['name']:"",
                //    "نام شهر" => (isset($b['addresses'][0]['province']['city']['name'])) ?$b['addresses'][0]['province']['city']['name']:""

            ];
        }
        return $alld;*/
    }

    public function list(Request $request)
    {

        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }
        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                }
            ]);
        return $customer->filter($request->all(), CustomerFilter::class)->get();
    }

    public function show($id)
    {
        $customer = Users::customer()
            ->with([
                'Addresses',
                'Contacts',
                'Areas',
                "Routes",
                'Countries',
                'Provinces',
                'Cities',
                'Group',
                'Photo',
                'PaymentMethodCustomer.PaymentMethod',
                'PriceClasses' => function ($query) {
                    $query->where('company_id', auth('api')->user()->company_id);
                },
                'Categories',
                'Referrals' => function ($query) {
                    $query->where('company_id', auth('api')->user()->company_id);
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerClass' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
            ])
            ->find($id);
        if (!isset($customer)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }

        $customer = $customer->setAppends([
            'status_translate',
        ]);
        $customerArray = $customer->toArray();
        $customerArray['countries'] = $customerArray['countries'][0] ?? NULL;
        $customerArray['provinces'] = $customerArray['provinces'][0] ?? NULL;
        $customerArray['categories'] = $customerArray['categories'][0] ?? NULL;
        $customerArray['cities'] = $customerArray['cities'][0] ?? NULL;
        $customerArray['price_class'] = $customerArray['price_classes'][0] ?? NULL;
        $customerArray['role'] = $customerArray['roles'][0] ?? NULL;
        return $customerArray;
    }

    public function states()
    {
        $data = [];
        $status = Users::STATUS;
        $status = array_map(function ($sub_status) {
            $sub_status['title'] = trans('translate.user.status.' . $sub_status["value"]);
            return $sub_status;
        }, $status);
        return response()->json(['status' => $status]);
    }

    public function changeStates(Request $request)
    {
        foreach ($request->id as $id) {
            $user = Users::find($id);
            if (!empty($user)) {
                $user->status = $request->value;
                $user->save();
            }
        }
        return [
            'status' => true,
            'message' => trans('messages.customer.customer.changeStatus'),
        ];
    }

    public function approveStates(ApproveStateRequest $request)
    {
        foreach ($request->user_ids as $id) {
            $user = Users::findOrFail($id);
            $user->approve = $request->status;
            $user->save();
        }
        return [
            'status' => true,
            'message' => trans('messages.customer.customer.changeStatus'),
        ];
    }


    public function destroy(DestroyRequest $request)
    {
        if (auth('api')->user()['kind'] == 'company')
            throw new CoreException('دسترسی غیر مجاز در سطح کمپانی');

        $ids = $request->id;
        $result = Users::Customer()
            ->whereIn('id', $ids)
            ->doesntHave('Orders')
            ->delete();

        $id_customer = "";
        foreach ($ids as $id) {
            $id_customer = $id_customer . "||" . $id;
        }

        Log::info('delete customers' . $id_customer . "  by user  " . auth('api')->user()->id);

        if ($result)
            return [
                'status' => true,
                'message' => trans('messages.customer.customer.destroy'),
            ];
        else return [
            'status' => false,
            'message' => 'مشتری دارای گردش مالی می باشد نمی توان حذف کرد',
        ];
    }

    public function update(UpdateRequest $request, $id)
    {


        $customer = Users::customer()
            ->with([
                'Addresses',
                'Contacts',
                'Countries',
                'Provinces',
                'Cities',
                'Areas',
                "Routes",
                'Categories',
                'Photo'
            ])->find($id);
        if (!isset($customer)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }



        $customer->email = $request->email;
        $customer->mobile_number = $request->mobile_number;
        $customer->manager_mobile_number = $request->manager_mobile_number;
        $customer->phone_number = $request->phone_number;

        if($request->national_id)
        $customer->national_id = $request->national_id;
        if ($request->referral_id)
            $customer->referral_id =  $request->referral_id;
        $customer->store_name = $request->store_name;
        if ($request->group_id)
            $customer->group_id = $request->group_id;

        if ($request->password)
            $customer->password = bcrypt($request->password);

        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;

        if (!empty($request->customer_group)) {
            $customer->customer_group = $request->customer_group;
        }

        if (!empty($request->customer_class)) {
            $customer->customer_class = $request->customer_class;
        }

        if (!empty($request->customer_grade)) {
            $customer->customer_grade = $request->customer_grade;
        }

        if (!empty($request->introduction_source)) {
            $customer->introduction_source = $request->introduction_source;
        }

        $customer->description = $request->description;



        $customer->photo_id = $request->photo_id;

        $customer->save();


        // Save location
        //            $customer->Countries()->sync(collect($request->countries)->all());
        $customer->Provinces()->sync($request->province);
        $customer->Cities()->sync($request->city);
        $customer->Areas()->sync($request->area);
        $customer->Routes()->sync($request->route);
        $customer->Categories()->sync($request->customer_category['id']);

        // Remove One to Many Question
        $remainId = collect($request->addresses)->pluck('id')->all();
        if ($remainId) {
            $diffId = $customer->addresses->pluck('id')
                ->diff($remainId)
                ->toArray();
            Address::whereIn('id', $diffId)->delete();
        }


        // Store Addresses
        foreach ($request->addresses as $address) {
            if (isset($address['id']) && $address['id'])
                $addressEntity = Address::find($address['id']);
            else
                $addressEntity = new Address();

            $addressEntity->address = $address['address'];
            $addressEntity->postal_code = $address['postal_code'] ?? '';
            $addressEntity->lat = $address['lat'] ?? "";
            $addressEntity->long = $address['long'] ?? "";

            $addressEntity->User()->associate($customer);

            $addressEntity->save();
        }


        // Remove One to Many Question
        $remainId = collect($request->contacts)->all();
        if ($remainId) {
            $diffId = $customer->contacts->pluck('id')
                ->diff($remainId)
                ->toArray();
            Contact::whereIn('id', $diffId)->delete();
        }


        if ($request->price_classes)
            $customer->PriceClasses()->sync($request->price_classes ?? NULL);

        PaymentMethodCustomer::where('customer_id', $customer->id)->delete();
        $data = [];
        if (!count($request['payment_method_id'])) {
            $paymentme = PaymentMethod::where('default', 1)->where('company_id', auth('api')->user()->id)->get();
            foreach ($paymentme as $payme) {
                $data[] = [
                    'customer_id' => $customer->id,
                    'payment_method_id' => $payme->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
        foreach ($request['payment_method_id'] as $paymentMethodId) {
            $data[] = [
                'customer_id' => $customer->id,
                'payment_method_id' => $paymentMethodId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        PaymentMethodCustomer::insert($data);

        return [
            'status' => true,
            'message' => trans('messages.customer.customer.update'),
            'id' => $customer->id,
        ];
    }

    public function store(StoreRequest $request)
    {

        try{
        $referralId = LinkCustomer::getRefrral($request->mobile_number, $request->phone_number);
        }
        catch (\Exception $e) {
        }

        $customer = new Users();
        $customer->email = $request->email;
        $customer->mobile_number = $request->mobile_number;
        $customer->phone_number = $request->phone_number;
        $customer->manager_mobile_number = $request->manager_mobile_number;
        $customer->national_id = $request->national_id;
        if ($request->group_id)
            $customer->group_id = $request->group_id;
        $customer->store_name = $request->store_name;
        if ($request->password)
            $customer->password = bcrypt($request->password);

        $customer->first_name = $request->first_name;
        $customer->kind = Users::KIND_CUSTOMER;
        $customer->last_name = $request->last_name;

        if (!empty($request->customer_group)) {
            $customer->customer_group = $request->customer_group;
        }

        if (!empty($request->customer_class)) {
            $customer->customer_class = $request->customer_class;
        }

        if (!empty($request->customer_grade)) {
            $customer->customer_grade = $request->customer_grade;
        }

        if (!empty($request->introduction_source)) {
            $customer->introduction_source = $request->introduction_source;
        }

        if (!empty($request->description)) {
            $customer->description = $request->description;
        }

        $customer->photo_id = $request->photo_id;

        if (isset($referralId) && (!empty($referralId))) {
            //$customer->referral_id = $referralId;
        }

        $customer->save();
        $customer->Provinces()->sync($request->province);
        $customer->Cities()->sync($request->city);
        $customer->Areas()->sync($request->area);
        $customer->Routes()->sync($request->route);
        $customer->Categories()->sync($request->customer_category['id']);
        if (!empty($request->price_classes))
            $customer->PriceClasses()->sync($request->price_classes);
        // Remove One to Many Question
        $remainId = collect($request->addresses)->pluck('id')->all();
        if ($remainId) {
            $diffId = $customer->addresses->pluck('id')
                ->diff($remainId)
                ->toArray();
            Address::whereIn('id', $diffId)->delete();
        }


        // Store Addresses
        foreach ($request->addresses as $address) {
            if (isset($address['id']) && $address['id'])
                $addressEntity = Address::find($address['id']);
            else
                $addressEntity = new Address();

            $addressEntity->address = $address['address'];
            $addressEntity->postal_code = $address['postal_code'] ?? '';
            $addressEntity->lat = $address['lat'] ?? "";
            $addressEntity->long = $address['long'] ?? "";

            $addressEntity->User()->associate($customer);

            $addressEntity->save();
        }


        //comment by behzad 
        //نمی دونم کاربرد کد چیه
/*
        // Remove One to Many Question
        $remainId = collect($request->contacts)->all();
        if ($remainId) {
            $diffId = $customer->contacts->pluck('id')
                ->diff($remainId)
                ->toArray();
            Contact::whereIn('id', $diffId)->delete();
        }

        $companyId = auth('api')->user()->id;
        $customerIds = $request->referral_id;

        $companyCustomersEntity = CompanyCustomer::CompanyId($companyId)
            ->ReferralId($customerIds)
            ->get()
            ->keyBy('referral_id');
        if (isset($companyCustomersEntity[$customerIds]) && $companyCustomersEntity[$customerIds])
            $companyCustomer = $companyCustomersEntity[$customerIds];
        else
            $companyCustomer = new CompanyCustomer();

        $companyCustomer->referral_id = $customerIds;
        $companyCustomer->email = $request['email'];
        $companyCustomer->mobile_number = $request['mobile_number'];
        $companyCustomer->phone_number = $request['phone_number'];
        $companyCustomer->store_name = $request['store_name'] ?? null;

        $companyCustomer->first_name = $request['first_name'];
        $companyCustomer->last_name = $request['last_name'];
        $companyCustomer->national_id = $request['national_id'];
        $companyCustomer->company_id = $companyId;

        //        $companyCustomer->price_class_id = $request['price_classes'];// $priceClass->id;

        $companyCustomer->address = [
            'address' => $request['address'],
            'lat' => $request['lat'],
            'long' => $request['lng'],
        ];

        $companyCustomer->save();
        $data = [];
        if (count($request['payment_method_id'])) {
            foreach ($request['payment_method_id'] as $paymentMethodId) {
                $data[] = [
                    'customer_id' => $customer->id,
                    'payment_method_id' => $paymentMethodId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        } else {
            $paymentme = PaymentMethod::where('default', 1)->where('company_id', auth('api')->user()->id)->get();
            foreach ($paymentme as $payme) {
                $data[] = [
                    'customer_id' => $customer->id,
                    'payment_method_id' => $payme->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
        PaymentMethodCustomer::insert($data);
*/
        return [
            'status' => true,
            'message' => trans('messages.api.company.customer.store'),
        ];
    }

    public function visitors(Request $request)
    {
        $customer_id = $request->customer_id;
        $id = $request->id;
        $name = $request->name;
        $customer = Users::customer()->with([
            "routes"
        ])->find($customer_id);
        if (!$customer) {
            throw new CoreException(' ویزیتوری برای این مشتری یافت نشد');
        }
        if (count($customer->routes) > 0 and $customer->routes[0]) {
            if (isset($customer->routes[0]->id)) {

                $routes_id = $customer->routes[0]->id;

                $visitors = Visitors::whereHas('routes', function ($query) use ($routes_id) {
                    $query->where('id', $routes_id);
                })->with(['user' => function ($query) use ($name) {
                    if ($name) {
                        $query->where(function ($q) use ($name) {
                            $q->where('first_name', 'like', '%' . $name . '%')->orWhere('last_name', 'like', '%' . $name . '%');
                        });
                    }
                }, 'routes' => function ($query) use ($routes_id) {
                    $query->where('id', $routes_id);
                }]);
                if ($id) {
                    $visitors->where('id', $id);
                }
                $visitors = $visitors->get();

                if (count($visitors) < 1) {
                    throw new CoreException(' ویزیتوری برای این مشتری یافت نشد');
                }
                return $visitors;
            }
        } else {
            throw new CoreException("مسیری برای مشتری ارسالی یافت نشد");
        }
    }

    public function routes()
    {
        if (empty(request()->get('route_ids')) and empty(request()->get('areas_id'))) {
            return [
                'status' => false,
                'message' => "شناسه مسیرها را وارد نمایید"
            ];
        }

        if (!is_array(request()->get('route_ids')) and !is_array(request()->get('areas_id'))) {
            return [
                'status' => false,
                'message' => "شناسه مسیرها را به صورت آرایه وارد نمایید"
            ];
        }

        $routeIds = request()->get('route_ids');
        $areas_id = request()->get('areas_id');


        if (request()->get('areas_id')) {
            $result = Users::with('Routes')
                ->where('kind', 'customer')
                ->whereHas('Routes', function ($query) use ($areas_id) {
                    $query->whereIn('area_id', $areas_id);
                })->jsonPaginate();
            return $result;
        }

        $result = Users::with('Routes')
            ->where('kind', 'customer')
            ->whereHas('Routes', function ($query) use ($routeIds) {
                $query->whereIn('route_id', $routeIds);
            })->jsonPaginate();
        return $result;
    }

    public function ImportFromExcel()
    {

        /*$date_create = new Verta('2021-09-30 03:33:21');
        return str_replace('-', '/', $date_create->formatDate());*/
        /*  $customer = Users::with(['Cities', 'Provinces'])->where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->where('status', 'active')
            ->whereHas('Provinces', function ($q) {
                $q->where('id', '8');
            })
            ->doesntHave('Cities')
            ->get();

        return $customer;*/

        //ini_set('memory_limit', '1024M');
        //ini_set('max_execution_time', 3800);
        // Excel::import(new UsersImport, public_path('/shno.xlsx'));

        //لیست محصولات
        //$url = "http://178.252.133.78";
        $url = "http://retailapi.kheilisabz.com";

        $url = $url . "/api/products/279640035";
        $username = "admin";
        $password = "123";


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_USERPWD => $username . ":" . $password,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    private function ISCompany()
    {
        if (auth('api')->user()['kind'] == 'admin')
            return 0;
        else
            return auth('api')->user()->company_id;
    }









    public function geo(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        if (auth('api')->user()->show_users_list == 0) {
            throw new CoreException('دسترسی به مشتریان برای شرکت مورد نظر غیرفعال می باشد');
        }
        $cities = array();
        if ($this->ISCompany()) {
            $cities = Users::with('Cities')->where('id', $this->ISCompany())->first()->Cities->pluck('id');
        }

        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'provinces',
                'cities',
                'Areas',
                "Products",
                "Orders",
                "Routes",
                "PriceClasses",
                'addresses',
                'IntroducerCode',
                'PaymentMethodCustomer',
                'categories' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGrade' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'CustomerGroup' => function ($query) {
                    $query->select('id', 'constant_fa');
                },
                'IntroductionSource' => function ($query) {
                    $query->select('id', 'constant_fa');
                }
            ]);


        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $customer = $customer->filter($request->all(), CustomerFilter::class);
        } else {
            $customer = $customer->whereCities($cities)->filter($request->all(), CustomerFilter::class);
        }

        if (request('from_date') && request('to_date')) {
            $customer = $customer->where('created_at', '>', request('from_date'))
                ->where('created_at', '<=', request('to_date'));
        }

        if (request('cities') && count(request('cities'))) {
            $iCities = array_map('intval', request('cities'));
            $customer = $customer->whereCities($iCities);
        }

        if (request('areas') && count(request('areas'))) {
            $iAreas = request('areas');
            $customer = $customer->whereHas('areas', function ($query) use ($iAreas) {
                $query->whereIn('id', $iAreas);
            });
        }

        if (request('routes') && count(request('routes'))) {
            $iRoutes = request('routes');
            $customer = $customer->whereHas('routes', function ($query) use ($iRoutes) {
                $query->whereIn('id', $iRoutes);
            });
        }

        if (request('users') && count(request('users'))) {
            $customer = $customer->whereIn('id', request('users'));
        }

        if (request('brands') && count(request('brands'))) {
            $iBrands = request('brands');
            $customer = $customer->whereHas('products', function ($query) use ($iBrands) {
                $query->whereIn('brand_id', $iBrands);
            });
        }

        if (request('products') && count(request('products'))) {
            $iProducts = request('products');
            $customer = $customer->whereHas('products', function ($query) use ($iProducts) {
                $query->whereIn('id', $iProducts);
            });
        }

        if (request('product_groups') && count(request('product_groups'))) {
            $iProductGroups = request('product_groups');
            $customer = $customer->whereHas('products', function ($query) use ($iProductGroups) {
                $query->whereIn('category_id', $iProductGroups);
            });
        }

        if (request('categories') && count(request('categories'))) {
            $iCategories = request('categories');
            $customer = $customer->whereHas('categories', function ($query) use ($iCategories) {
                $query->whereIn('id', $iCategories);
            });
        }

        if (request('grades')) {
            $customer = $customer->whereIn('customer_grade', request('grades'));
        }

        if (request('active')) {
            if (request('active') == true) {
                $customer = $customer->where('status', 'active');
            }

            if (request('active') == false) {
                $customer = $customer->where('status', 'inactive');
            }
        }

        if (request('order_register_source')) {
            $orderRegisterSource = request('order_register_source');
            $customer = $customer->whereHas('orders', function ($query) use ($orderRegisterSource) {
                $query->where('registered_source', $orderRegisterSource);
            });
        }

        if (request('categories') && count(request('categories'))) {
            $iCategories = request('categories');
            $customer = $customer->whereHas('categories', function ($query) use ($iCategories) {
                $query->whereIn('id', $iCategories);
            });
        }


        if (request('reason_for_not_visiting')) {
            if (request('reason_for_not_visiting') == true) {
                $customer = $customer->whereHas('ReasonForNotVisitings')->whereDoesntHave('orders');
            }

            if (request('reason_for_not_visiting') == false) {
                $customer = $customer->whereDoesntHave('ReasonForNotVisitings')->whereHas('orders');
            }
        }

        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            ini_set('memory_limit', '512M');
            $customer = $customer->get();
        }

        ini_set('memory_limit', '512M');
        $customer = $customer->get();

        $customerIds = [];
        foreach ($customer->toArray() as $cu) {
            $customerIds[] = $cu['id'];
        }

        $orders = Order::select('customer_id', 'created_at')
            ->join(DB::raw('(Select max(id) as id from orders group by customer_id) LatestMessage'), function ($join) {
                $join->on('orders.id', '=', 'LatestMessage.id');
            })->whereIN('customer_id', $customerIds)->get()->toArray();

        $customerOrders = [];
        foreach ($orders as $order) {
            $customerOrders[$order['customer_id']] = $order['created_at'];
        }

        foreach ($customer as $index => $cu) {
            if (isset($customerOrders[$cu['id']])) {
                $v = new Verta($customerOrders[$cu['id']]);
                $customer[$index]['order_last_date'] = str_replace('-', '/', $v->formatDate());
            }

            if (!empty($cu['categories'][0]['constant_fa'])) {
                $customer[$index]['category_name'] = $cu['categories'][0]['constant_fa'];
            }

            $customer[$index]['address'] = (isset($cu['addresses'][0]['address'])) ? $cu['addresses'][0]['address'] : "null";
        }

        $jsongFile = time() . '.geojson';
        File::put(public_path('/upload/json/' . $jsongFile), $this->geoJson($customer));
        return [
            'status' => true,
            'url' => url('/') . '/upload/json/' . $jsongFile
        ];


        return $customer;
    }
}
