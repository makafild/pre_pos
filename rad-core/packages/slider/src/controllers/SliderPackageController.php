<?php

namespace core\Packages\slider\src\controllers;


use Core\Packages\gis\City;
use Core\Packages\gis\Areas;
use Illuminate\Http\Request;
use Core\Packages\gis\Routes;
use Core\Packages\gis\Province;
use Core\Packages\slider\Slider;
use App\ModelFilters\SliderFilter;
use Core\Packages\common\Constant;
use App\ModelFilters\ConstantFilter;
use Hekmatinasser\Verta\Facades\Verta;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\slider\src\request\ListRequest;
use Core\Packages\slider\src\request\StoreRequest;
use Core\Packages\slider\src\request\UpdateRequest;
use Core\Packages\slider\src\request\DestroyRequest;
use Core\Packages\slider\src\request\ChangeStatusRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */


class SliderPackageController extends CoreController
{
    private $_fillable = [
        'constant_en',
        'constant_fa',
        'kind',
    ];
    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }
        $sliders = Slider::select('sliders.*')
            ->with([
                'file',
                'Provinces',
                'Areas',
                'cities',
                'Route',
            ]);
        $sliders = $sliders->filter($request->all(), SliderFilter::class)->orderBy($sort, $order)->jsonPaginate($limit);
        return $sliders;
    }

    public function show($id)
    {
        $result = Slider::where('id',$id)->with(['Cities',
        'Provinces',
        'Countries',
        "Areas",
        "Route",
        "Product",
        "Company",
        "file"])->first();
        return $result;
    }

    public function store(StoreRequest $request)
    {


        // dd($request->photo_id);
        $Slider = new Slider();
        $Slider->start_at = $request->start_at;
        $Slider->end_at = $request->end_at;
        $Slider->file_id = $request->photo_id;
        $Slider->company_id = $request->company_id;
        $Slider->product_id = $request->product_id;
        $Slider->title = $request->title;
        $Slider->description = $request->description;
        $Slider->link = $request->link;
        $Slider->kind = $request->kind;
        $Slider->status = Slider::STATUS_ACTIVE;
        $Slider->save();
        if ($request->has('cities')) {
            foreach ($request->cities as $city) {
                $city_info = City::find($city);
                $Slider->Cities()->toggle($city_info);
            }
        }
         if ($request->has('areas')) {
            foreach ($request->areas as $area) {
                $Areas_info = Areas::find($area);
                $Slider->Areas()->toggle($Areas_info);

            }
        }
        if ($request->has('provinces')) {
            foreach ($request->provinces as $Province) {
                $Provinces_info =  Province::find($Province);
                $Slider->Provinces()->toggle($Provinces_info);
            }
        }
        if ($request->has('route')) {
            foreach ($request->route as $route) {
                $routes_info =  Routes::find($route);
                $Slider->Route()->toggle($routes_info->id);
            }
        }


        return [
            'status'  => true,
            'message' => trans('messages.setting.constant.store'),
            'id'      => $Slider->id,
        ];
    }

    public function destroy(DestroyRequest $request)
    {
        Slider::destroy($request->id);
        return [
            'status'  => true,
            'message' => trans('messages.setting.constant.destroy'),
        ];
    }
    public function update(UpdateRequest $request, $id)
    {

        $Slider = Slider::find($id);
        if (!$Slider) {
            throw new CoreException('اسلایدری یافت نشد!');
        }
        $Slider->start_at =$request->start_at;
        $Slider->end_at =  $request->end_at;
        $Slider->file_id = $request->photo_id;
        $Slider->company_id = $request->company_id;
        $Slider->title = $request->title;
        $Slider->description = $request->description;
        $Slider->product_id = $request->product_id;
        $Slider->link = $request->link;
        $Slider->kind = $request->kind;
        $provinces = Province::find($request->provinces);
        $Slider->save();


        if ($request->has('cities')) {
            $Slider->Cities()->detach();
            foreach ($request->cities as $city) {
                $city_info = City::find($city);
                $Slider->Cities()->toggle($city_info);
            }
        }
         if ($request->has('areas')) {
            $Slider->Areas()->detach();
            foreach ($request->areas as $area) {
                $Areas_info = Areas::find($area);
                $Slider->Areas()->toggle($Areas_info);

            }
        }
        if ($request->has('provinces')) {
            $Slider->Provinces()->detach();
            foreach ($request->provinces as $Province) {
                $Provinces_info =  Province::find($Province);
                $Slider->Provinces()->toggle($Provinces_info);
            }
        }
        if ($request->has('route')) {
            $Slider->Route()->detach();
            foreach ($request->route as $route) {
                $routes_info =  Routes::find($route);
                $Slider->Route()->toggle($routes_info->id);
            }
        }


        return [
            'status'  => true,
            'message' => trans('messages.setting.constant.store'),
            'id'      => $Slider->id,
        ];
    }


    public function changeStatus(ChangeStatusRequest $request)
    {
        foreach ($request->id as $id) {
            $slider = Slider::find($id);
//            if ($company->isInactive()) {
//                foreach ($company->tokens as $token) {
//                    $token->revoke();
//                }
//                $company->CompanyToken->delete();
//            }

            $slider->status = $request->value;
            $slider->save();
        }


        return [
            'status' => true,
            'message' => trans('اسلایدر با موفقیت وییرایش شد'),
        ];
    }

}
