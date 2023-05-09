<?php

namespace core\Packages\coupon\src\controllers;




use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use Core\Packages\coupon\Coupon;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\coupon\src\request\StoreRequest;
use Core\Packages\user\Users;

use function Siler\Functional\find;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class CouponPackageController extends CoreController
{

    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {

        $coupons = Coupon::with('company');
        if (auth('api')->user()['kind'] == "company")
            $coupons->whereHas('company', function($query){
                $query->where('company_coupons.company_id', auth('api')->user()['company_id']);
            });



        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $coupons = $coupons->filter($request->all())->get();
        } else {
            $coupons = $coupons->filter($request->all())->jsonPaginate($limit);

        }

        return $coupons;
    }

    public function store(StoreRequest $request)
    {

        $coupon_end_at = $request->coupon_end_at;

        $start_at = $request->coupon_start_at;


        $coup = new Coupon();
        $coup->coupon = $request->coupon;
        $coup->end_at = $coupon_end_at;
        $coup->start_at = $start_at;
        $coup->percentage = $request->coupon_percentage;
        $coup->discount_max = $request->discount_max;
        $coup->discount_min = $request->discount_min;

        $coup->kind = auth('api')->user()['kind'];

        if (auth('api')->user()['kind'] == "company") {
            $companys = Users::whereIn('id', [auth('api')->user()['company_id']])->get();
            $coup->company_id = auth('api')->user()['company_id'];


        } else {
            $companys = Users::whereIn('id', $request->company)->get();
            $coup->company_id =$request->company[0];


        }
$coup->save();


        $coup->company()->sync($companys->pluck('id'));


        return [
            'status' => true,
            'message' => 'کد تخفیف با موفقیت ایجاد شد'
        ];
    }

    public function show($id)
    {
        // $company = User::class
        $coupon = Coupon::with('company')->find($id);
        return $coupon;
    }



    public function update(Request $request, $id)
    {
        $coup = new Coupon();
        $coupon_end_at = $request->coupon_end_at;

        $start_at =$request->coupon_start_at;

        $coup = Coupon::findOrFail($id);
        $coup->coupon = $request->coupon;
        $coup->end_at = $coupon_end_at;
        $coup->start_at = $start_at;
        $coup->save();
        if ($coup->percentage <= 100) {
            $coup->percentage = $request->coupon_percentage;
        } else {
            return [
                'status' => false,
                'message' => 'مقدار درصد تخفیف نادرست است'
            ];
        }

        $coup->discount_max = $request->discount_max;
        $coup->discount_min = $request->discount_min;
        $coup->save();
        if (auth('api')->user()['kind'] == "company") {
            $companys = Users::whereIn('id', [auth('api')->user()['company_id']])->get();
        } else {
            $companys = Users::whereIn('id', $request->company)->get();
        }

        $coup->company()->sync($companys->pluck('id'));
        return [
            'status' => true,
            'message' => 'کد تخفیف با موفقیت ویرایش شد '
        ];
        //  +
    }

    public function destroy(Request $request , Coupon $coup)
    {

        $coup->secureDelete($request->ids , ['Orderss']);
    }
}


    /* @param  int  $id
     * @return \Illuminate\Http\Response
     */
