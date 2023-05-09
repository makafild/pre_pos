<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Http\Requests\api\Company\v1\PriceClass\StorePriceClassRequest;
use App\Models\User\PriceClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceClassController extends Controller
{

    public function list()
    {
        $priceClasses = PriceClass::where('company_id', auth('mobile')->user()->company_id)
            ->latest()->get();
        return $priceClasses;
    }
    public function list_all()
    {
        $priceClasses = PriceClass::all();
        return $priceClasses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePriceClassRequest $request)
    {
        $priceClassIds = [];
        foreach ($request->price_classes as $price_class) {

            $priceClass = new PriceClass();
            $priceClass->title = $price_class['title'];
            $priceClass->company_id = auth('mobile')->user()->company_id;
            $priceClass->referral_id = $price_class['referral_id'];
            $priceClass->save();

            $priceClassIds[] = $priceClass->id;
        }

        return [
            'status'  => true,
            'message' => trans('messages.user.price_class.store'),
            'id'      => $priceClassIds,
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(StorePriceClassRequest $request)
    {
        $priceClassIds = [];
        foreach ($request->price_classes as $price_class) {

            /** @var PriceClass $priceClass */
            $priceClass = PriceClass::ReferralId($price_class['referral_id'])->first();
            $priceClass->title = $price_class['title'];
            $priceClass->company_id = auth('mobile')->user()->company_id;
            $priceClass->referral_id = $price_class['referral_id'];
            $priceClass->save();

            $priceClassIds[] = $priceClass->id;
        }

        return [
            'status'  => true,
            'message' => trans('messages.user.price_class.update'),
            'id'      => $priceClassIds,
        ];
    }
}
