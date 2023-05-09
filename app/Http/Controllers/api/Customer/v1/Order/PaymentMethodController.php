<?php

namespace App\Http\Controllers\api\Customer\v1\Order;

use App\Http\Requests\api\Customer\v1\Order\StoreOrderRequest;
use App\Models\Order\Coupon;
use App\Models\Order\CouponCustomer;
use App\Models\Order\Detail;
use App\Models\Order\Order;
use App\Models\Order\PaymentMethod;
use App\Models\Order\VisitTour;
use App\Models\Product\Product;
use App\Models\Product\Promotions;
use App\Models\User\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {

    }


    public function index(Request $request)
    {
        $paymentMethods = PaymentMethod::whereHas(
            'PaymentMethodCustomer', function ($query) {
            $query->where('customer_id', '=', auth()->id());
        })

                ->orWhere('payment_methods.default', '=', '1')

            ->with([
            'PaymentMethodCustomer' => function ($query) {
                $query->where('customer_id', '=', auth()->id());
            }]);

        if ($request->comapny_ids)
            $paymentMethods = $paymentMethods->whereIn('company_id', $request->comapny_ids);

        $paymentMethods = $paymentMethods->get();
        if (!empty($paymentMethods)) {
            $data = [];
            foreach ($paymentMethods->toArray() as $paymentMethod) {
                $data[] = [
                    'id' => $paymentMethod['id'],
                    'company_id' => $paymentMethod['company_id'],
                    'discount' => $paymentMethod['discount'],
                    'discount_max' => $paymentMethod['discount_max'],
                    'payment_method_id' => $paymentMethod['payment_method_id'],
                    'payment_method' => [
		        'id' => $paymentMethod['id'],
                        'constant_fa' => $paymentMethod['constant_fa'],
                        'constant_en' => $paymentMethod['constant_en'],
			'kind' => 'payment_method',
			'kind_translate' => 'روش پرداخت',
                    ],
                ];
            }
            $paymentMethods = $data;
        }
        return $paymentMethods;
    }
}
