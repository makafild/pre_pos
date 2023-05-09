<?php

namespace Core\Packages\notification\src\controllers;


use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Illuminate\Http\Request;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Core\Packages\gis\Province;
use Core\Packages\common\Constant;
use App\ModelFilters\ConstantFilter;
use Hekmatinasser\Verta\Facades\Verta;
use Core\System\Exceptions\CoreException;
use Core\Packages\notification\Notification;
use Core\System\Http\Controllers\CoreController;
use App\Events\Notification\NotificationStoredEvent;
use Core\Packages\notification\src\request\StoreRequest;
use Core\Packages\notification\src\request\UpdateRequest;
use Core\Packages\notification\src\request\DestroyRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */


class NotificationPackageController extends CoreController
{

    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        $limit = 10;
        if (isset($request->limit)) {
            $limit = $request->limit;
        }
        $Notification = Notification::with([
            'customer',
            'Price_classes',
            'City',
            'Areas',
            'Routes',
            'Provinces'
        ]);
        if ($request->id)
            $Notification->where('id', $request->id);

        if ($request->link)
            $Notification->where('link', $request->link);
        if ($request->message) {
            $keywords = explode(' ', $request->message);
            foreach ($keywords as $keyword) {
                $Notification->where('message', 'like', '%' . $keyword . '%');
            }
        }
        if ($request->title) {
            $keywordst = explode(' ', $request->title);
            foreach ($keywordst as $keyword) {
                $Notification->where('message', 'like', '%' . $keyword . '%');
            }
        }



        if ($request->provinces) {
            $provinces = $request->provinces;
            $Notification->whereHas('Provinces', function ($query) use ($provinces) {
                $query->where('id', $provinces);
            });
        }
        if ($request->city) {
            $city = $request->city;
            $Notification->whereHas('City', function ($query) use ($city) {
                $query->where('id', $city);
            });
        }




        return $Notification->orderBy('created_at', 'desc')->jsonPaginate($limit);
    }

    public function show($id)
    {
        $Notification = Notification::with([
            'City',
            'customer',
            'Price_classes',
            'Areas',
            'Routes',
            'Provinces'
        ])->where('id', $id);
        return $Notification->first();
    }

    public function store(StoreRequest $request)
    {



        $notification             = new Notification();
        $notification->title      = $request->title;
        $notification->message    = $request->message;
        $notification->link       = $request->link;
        $notification->kind       = $request->kind;
        $notification->sms       = $request->sms;
        $notification->company       = $request->company;
        $notification->product       = $request->product;
        if ($request->date_start)
            $notification->date_start  = Verta::parse($request->date_start)->DateTime();
        if ($request->time_start)
            $notification->time_start  = $request->time_start;
        $notification->save();

        if ($request->has('price_classes')) {
            foreach ($request->price_classes as $price_classes) {
                $constant_info = Constant::find($price_classes);
                $notification->City()->toggle($constant_info);
            }
        }



        if ($request->has('areas')) {
            foreach ($request->areas as $area) {
                $Areas_info = Areas::find($area);
                $notification->Areas()->toggle($Areas_info);
            }
        }
        if ($request->has('provinces')) {
            foreach ($request->provinces as $Province) {
                $Provinces_info =  Province::find($Province);
                $notification->Provinces()->toggle($Provinces_info);
            }
        }
        if ($request->has('route')) {
            foreach ($request->route as $route) {
                $routes_info =  Routes::find($route);
                $notification->Routes()->toggle($routes_info->id);
            }
        }
        if ($request->has('customers')) {
            foreach ($request->customers as $customers) {
                $customers_info =  Users::find($customers);
                $notification->customer()->toggle($customers_info->id);
            }
        }
        //event(new NotificationStoredEvent($notification));

        return [
            'status'  => true,
            'message' => 'درخواست با موفقیت ثبت شد.',
        ];
    }

    public function destroy(Request $request)
    {
        if (isset($request->ids))
            Notification::destroy($request->ids);
        return [
            'status' => true,
            'message' => trans('با موفقیت حذف شد'),
        ];
    }

    public function update(UpdateRequest $request, $id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            throw new CoreException(' یافت نشد!');
        }

        $notification->title      = $request->title;
        $notification->message    = $request->message;
        $notification->link       = $request->link;
        $notification->sms       = $request->sms;
        $notification->date_start  = $request->date_start;
        $notification->time_start  = $request->time_start;
        $notification->save();

        if ($request->has('price_classes')) {
            $notification->Price_classes()->detach();
            foreach ($request->price_classes as $price_classes) {
                $constant_info = Constant::find($price_classes);
                $notification->Price_classes()->toggle($constant_info);
            }
        }

        if ($request->has('cities')) {
            $notification->City()->detach();
            foreach ($request->cities as $city) {
                $city_info = City::find($city);
                $notification->City()->toggle($city_info);
            }
        }
        if ($request->has('areas')) {
            $notification->Areas()->detach();
            foreach ($request->areas as $area) {
                $Areas_info = Areas::find($area);
                $notification->Areas()->toggle($Areas_info);
            }
        }
        if ($request->has('provinces')) {
            $notification->Provinces()->detach();
            foreach ($request->provinces as $Province) {
                $Provinces_info =  Province::find($Province);
                $notification->Provinces()->toggle($Provinces_info);
            }
        }
        if ($request->has('route')) {
            $notification->Routes()->detach();
            foreach ($request->route as $route) {
                $routes_info =  Routes::find($route);
                $notification->Routes()->toggle($routes_info->id);
            }
        }
        if ($request->has('customers')) {
            $notification->customer()->detach();
            foreach ($request->customers as $customers) {
                $customers_info =  Users::find($customers);
                $notification->customer()->toggle($customers_info->id);
            }
        }
        //event(new NotificationStoredEvent($notification));

        return [
            'status'  => true,
            'message' => 'درخواست با موفقیت ثبت شد.',
        ];
    }

    public function states()
    {
    }
}
