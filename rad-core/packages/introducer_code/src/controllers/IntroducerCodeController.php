<?php

namespace core\Packages\introducer_code\src\controllers;

use Core\Packages\introducer_code\IntroducerCode;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\introducer_code\src\request\IntroducerCodeRequest;
use Core\Packages\introducer_code\src\request\IntroducerCodeDestroyRequest;
use Illuminate\Http\Request;


class IntroducerCodeController extends CoreController
{

    private $_fillable = [
        'code',
        'title',
        'company_id'
    ];

    public function list(Request $request,IntroducerCode $intro ,$sort = "id", $order = "desc", $limit = 10)
    {

         /* $result = IntroducerCode::_()->list($request, $sort, $order , $limit );
        return $this->responseHandler2($result);*/

        // dd($request->all());

          $companyId = auth('api')->user()->company_id;
        $result =IntroducerCode::with(['Company']);
        if ($companyId) {
            $result->where('company_id', $companyId);
        }


        if ($request->has('paginate') && $request->get('paginate') == 'false') {
            $result = $result->filter($request->all())->get();
        }


        return $intro->with(['Company' => function($q){
            // $q->select('id' , 'name_fa');
        }])->filter($request->all())->orderBy('id','desc')->jsonPaginate($limit);

        }








        // $companyId = auth('api')->user()->company_id;
        // $result =IntroducerCode::with('company');
        // if ($companyId) {
        //     $result->where('company_id', $companyId);
        // }


        // if ($request->has('paginate') && $request->get('paginate') == 'false') {
        //     $result = $result->orderBy('$sort_arr', 'desc')->get();
        // } else {

        //     if ($request->has('sort')) {
        //         foreach ($sort_arr as $key => $value)
        //             $cop = $result->get();
        //         // dd( $cop[0]->company);
        //         if ($cop[0]->$key == true) {
        //             $result = $result->orderBy($key, $value);
        //         }
        //         if ($cop[0]->$key != true) {

        //             $result = $result->first()->company->orderBy($key, $value);

        //         }
        //     }
        //     return $result->jsonPaginate($limit);
        // }

    // }

    public function show($id)
    {

        $result = IntroducerCode::_()->show($id);
        return $this->responseHandler2($result);
    }

    public function store(IntroducerCodeRequest $request)
    {
        $payload = $request->only($this->_fillable);
        $result = IntroducerCode::_()->store($payload);

        return [
            'status' => empty($result->message) ? false : true,
            'message' => !empty($result->message) ? $result->message : [],
        ];
    }

    public function update(IntroducerCodeRequest $request, $id)
    {
        $payload = $request->only($this->_fillable);
        $result = IntroducerCode::_()->updateR($payload, $id);
        return $this->responseHandler2($result);
    }

    public function destroy(IntroducerCodeDestroyRequest $request , IntroducerCode $intco)
    {

        $intco->secureDelete($request->ids , ['Customer']);
    }
}
