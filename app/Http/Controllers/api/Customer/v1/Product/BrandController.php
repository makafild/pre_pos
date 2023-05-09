<?php

namespace App\Http\Controllers\api\Customer\v1\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Brand;

class BrandController extends Controller
{
    public function index()
    {

        return $brand= Brand::with('photo' , 'Companies')->get();







//         $companyId = request('company_id');
//         $categoryIds = request('category_ids');
//         $search = request('search');

// //		if (!is_array($categoryIds))
// //			$categoryIds = [$categoryIds];

//         $brands = Brand::with([
//             'photo',
//             'Companies',
//         ]);
//         ->whereHas('Products', function ($query) use ($categoryIds) {
//             if ($categoryIds)
//                 $query->where('category_id', $categoryIds);

//             $query->Active()->whereHas('company', function ($query) {
//                 $query->MyCompany();
//             });
//         })
//         ->withCount([
//             'products' => function ($query) use ($companyId) {
//                 $query->Active()->whereHas('company', function ($query) {
//                     $query->MyCompany();
//                 });

//                 if ($companyId)
//                     $query->where('company_id', $companyId);
//             },
//         ]);
//         if ($search) {
//             $brands = $brands->where('search', 'like', "%{$search}%");
//         }

//         if ($companyId)

//             $brands = $brands->whereHas('Companies', function ($query) use ($companyId) {
//                 $query->where('id', $companyId);
//             });
//         $brands = $brands->get();
//         $brandOutput = [];
//         foreach ($brands->toArray() as $brand) {
//             if ($brand['products_count'] > 0) {
//                 $brandOutput[] = $brand;
//             }
//         }


//         foreach ($brandOutput as &$item) {
//             $item['companies'] = array_column($item['companies'], 'id');
//         }

//         return $brandOutput;
    }
    public function jwtindex()
    {


        $companyId = request('company_id');
        $categoryIds = request('category_ids');
        $search = request('search');

//		if (!is_array($categoryIds))
//			$categoryIds = [$categoryIds];

        $brands = Brand::with([
            'photo',
            'Companies',
        ])->whereHas('Products', function ($query) use ($categoryIds) {
            if ($categoryIds)
                $query->where('category_id', $categoryIds);

            $query->Active();
        })->withCount([
            'products' => function ($query) use ($companyId) {
                $query->Active()->whereHas('company', function ($query) {
                    //$query->MyCompany();
                });

                if ($companyId)
                    $query->where('company_id', $companyId);
            },
        ]);
        if ($search) {
            $brands = $brands->where('search', 'like', "%{$search}%");
        }

        if ($companyId)

            $brands = $brands->whereHas('Companies', function ($query) use ($companyId) {
                $query->where('id', $companyId);
            });
        $brands = $brands->get();
        $brandOutput = [];
        foreach ($brands->toArray() as $brand) {
            if ($brand['products_count'] > 0) {
                $brandOutput[] = $brand;
            }
        }


        // foreach ($brandOutput as &$item) {
        //     $item['companies'] = array_column($item['companies'], 'id');
        // }

        return $brandOutput;
    }
}
