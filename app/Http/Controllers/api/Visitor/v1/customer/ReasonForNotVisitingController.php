<?php

namespace App\Http\Controllers\api\Visitor\v1\customer;

use App\Http\Requests\Visitor\ReasonForNotVisitingRequest;
use App\Models\User\ReasonForNotVisiting;
use App\Http\Controllers\Controller;

class ReasonForNotVisitingController extends Controller
{
    public function store(ReasonForNotVisitingRequest $request){
       $reason= ReasonForNotVisiting::create([
            'visitor_id'=>$request->visitor_id,
            'reson_id'=>$request->reson_id,
            'customer_id'=>auth('api')->user()->id,
            'description'=>$request->description
        ]);

        return [
            'status'       => false,
            'message' => "دلیل عدم ویزیت با موفقیت ثبت شد",
        ];
    }
}
