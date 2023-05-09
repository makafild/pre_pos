<?php

namespace App\Http\Controllers\ws;

use Core\Packages\user\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $userInfo = Users::
            with(['Provinces', 'Cities', 'Addresses','Categories'])
            ->where('status','active')
            ->where('kind','customer')
            ->get();

        if (empty($userInfo)) {
            return response()->json([
                'hasError' => false,
                'count' => 0,
                'message' => [],
                'result' => []
            ], 200);
        }

        $data=[];
        foreach ($userInfo->toArray() as $user){
            $data[]=[
                'local_id'=>$user['id'],
                'referral_id'=>$user['referral_id'],
                'mobile'=>$user['mobile_number'],
                'phone'=>$user['phone_number'],
                'fname'=>$user['first_name'],
                'lname'=>$user['last_name'],
                'updated_at'=>$user['updated_at'],
                'provinces'=>$user['provinces'],
                'cities'=>$user['cities'],
                'addresses'=>$user['addresses'],
                'categories'=>$user['categories'],
            ];
        }
        return response()->json([
            'hasError' => false,
            'count' => count($data),
            'message' => [],
            'result' => $data
        ], 200);
    }
}
