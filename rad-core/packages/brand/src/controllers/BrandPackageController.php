<?php

namespace core\Packages\brand\src\controllers;


use Illuminate\Http\Request;
use Core\Packages\user\Users;
use Core\Packages\brand\Brand;

use Core\Packages\common\File;
use App\ModelFilters\BrandFilter;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\brand\src\request\BrandRequest;
use Core\Packages\brand\src\request\DestroyBrandRequest;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */


class BrandPackageController extends CoreController
{
    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_last($sort_arr);
            $order = $sort_arr[$sort];
        }

        $brands = Brand::
               with('Companies')->filter($request->all(), BrandFilter::class);

        if (auth('api')->user()->kind == 'company') {
            $companyId=auth('api')->user()->company_id;
            $brands = $brands->whereHas('Companies', function ($query) use ($companyId) {
                $query->where('id', $companyId);
            });
        }

        $brands = $brands->orderBy($sort,$order)->jsonPaginate($limit);
        return $brands;
    }
    public function store(BrandRequest $request)
    {
        $brand           = new Brand();
        $brand->name_en  = $request->name_en;
        $brand->name_fa  = $request->name_fa;
        $brand->photo_id = $request->photo_id;
        $brand->save();

        return [
            'status'  => true,
            'message' => trans('messages.product.brand.store'),
            'id'      => $brand->id,
        ];
    }
    public function show($id)
    {
        $result = Brand::find($id);
        if (!isset($result)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }
        $result= Brand::with('photo', 'companies')->where('id',$id)->first();
        return $result;
    }
    public function update(BrandRequest $request, $id)
    {
        /** @var Brand $brand */
        $brand = Brand::findOrFail($id);


        $brand->name_en  = $request->name_en;
        $brand->name_fa  = $request->name_fa;
        $brand->photo_id = $request->photo_id;
        $brand->save();

        return [
            'status'  => true,
            'message' => trans('messages.product.brand.update'),
            'id'      => $brand->id,
        ];
    }
    public function destroy(DestroyBrandRequest $request , Brand $brand)
    {
       

        $brand->secureDelete($request->brands , ['Products','rewards','Companies']);
    }

    public function list()
    {
        if (auth()->user()->company_id) {
            //return auth()->user()->CompanyUser->brands;
            return Users::with('Brands')->where('company_id',auth()->user()->company_id)->get();
        }

        /** @var Brand[] $brands */
        $brands = Brand::with('photo')->get();

        return $brands;
    }


}
