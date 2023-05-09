<?php

namespace  Core\System\Http\Controllers;

use App\Http\Controllers\Controller;
use const Siler\Http\flash;

class BaseController extends Controller
{

    public function responseHandler($data,$type = null)
    {
        $messages = '';
        if (!empty($data->hasError) && $data->hasError == true) {
            $messages = $data->message;
        } else {
            if (!empty($data->error)) {
                $messages = $data->error_description;
            }else{
                if (isset($data->result->id) and isset($type) and $type == "create"){
                    return [
                        'status'  => true,
                        'message' =>"عملیات با موفقیت ثبت شد",
                        'id'      => $data->result->id,
                    ];
                }elseif(isset($data->result->id) and isset($type) and $type == "delete"){
                    return [
                        'status'  => true,
                        'message' =>"ایتم با موفقیت حذف شد",
                    ];
                }elseif(isset($data->result->id) and isset($type) and $type == "update"){
                    return [
                        'status'  => true,
                        'message' =>"ایتم با موفقیت بروزرسانی شد",
                    ];
                }else{

                }

            }
        }

        if (isset($data->message) and empty($messages) ){
            $messages = $data->message;
        }
        if (!empty($messages) && !is_array($messages)) {
            $messages = [
                $messages
            ];
        }
        $responseRecord = !empty($data->result) ? $data->result : [];
        return response()->json($responseRecord, 200, ['Content-Type' => 'application/json;charset=utf8'],
        JSON_UNESCAPED_UNICODE);
    }

    function responseHandler2($data)
    {
        $responseRecord = [
            'status' => !empty($data->message) ? true : true,
            'message' => !empty($data->message) ? $data->message : [],
            'count' => !empty($data->count) ? $data->count :0,
            'result' => !empty($data->result) ? $data->result : []
        ];

        return response()->json($responseRecord, 200, ['Content-Type' => 'application/json;charset=utf8'],
            JSON_UNESCAPED_UNICODE);    }


}
