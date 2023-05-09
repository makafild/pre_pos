<?php

namespace core\Packages\not_visited\src\controllers;

use Illuminate\Http\Request;
use App\Models\User\ReasonForNotVisiting;
use Core\Packages\not_visited\NotVisited;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\not_visited\src\request\NotVisitedStoreRequest;

class NotVisitedController extends CoreController
{



    public function index(Request $request)
    {
        if (!auth('api')->user()->company_id) {
            throw new CoreException('این آیتم فقط برای کمپانی ها می باشد');
        }

        $company_id = auth('api')->user()->company_id;
        $limt = 10;
        if (isset($request->limit))
            $limt = $request->limit;
        $not_visited = NotVisited::where('company_id', auth('api')->user()->company_id)->orderBy('created_at', 'desc')
            ->jsonPaginate($limt);
        return $not_visited;
    }


    public function show($id)
    {
        if (!auth('api')->user()->company_id) {
            throw new CoreException('این آیتم فقط برای کمپانی ها می باشد');
        }

        $company_id = auth('api')->user()->company_id;

        $not_visited = NotVisited::where('company_id', auth('api')->user()->company_id)
            ->where('id', $id)
            ->first();
        return $not_visited;
    }


    public function destroy(Request $request)
    {
        //     if (isset($request->ids))
        //         NotVisited::destroy($request->ids);

        $not_visi = NotVisited::whereIn('id', $request->ids);
        if ($this->ISCompany())
            $not_visi->where('company_id', $this->ISCompany());
        if ($not_visi->count()) {
            $not_visi->delete();
            return [
                'status' => true,
                'message' => trans('با موفقیت حذف شد'),
            ];
        }
        return [
            'status' => false,
            'message' => 'با عرض پوزش یافت نشد',
        ];
    }



    public function store(NotVisitedStoreRequest $request)
    {
        if (!auth('api')->user()->company_id) {
            return [
                'status' => false,
                'message' => trans('شما به این بخش دسترسی ندارید'),
            ];
        }

        $not_visited = new NotVisited();
        $not_visited->message = $request->message;
        $not_visited->company_id =  auth('api')->user()->company_id;
        $not_visited->save();

        return [
            'status'  => true,
            'message' => trans('با موفقیت ثبت شد'),
            'id'      => $not_visited->id,
        ];
    }


    public function update(NotVisitedStoreRequest $request, $id)
    {
        $not_visited = NotVisited::where('id', $id);
        if ($this->ISCompany())
            $not_visited->where('company_id', $this->ISCompany());

        if ($not_visited->count()) {
            $not_visited=$not_visited->first();
            $not_visited->message = $request->message;
            $not_visited->save();

            return [
                'status'  => true,
                'message' => trans('با موفقیت ویرایش شد'),
                'id'      => $not_visited->id,
            ];
        } else {
            return [
                'status'  => false,
                'message' => trans( 'با عرض پوزش یافت نشد'),
            ];
        }
    }


    public function list_message(Request $request)
    {
        $limt = 10;
        if (isset($request->limit))
            $limt = $request->limit;
        if (!auth('api')->user()->company_id) {
            return [
                'status' => false,
                'message' => trans('شما به این بخش دسترسی ندارید'),
            ];
        }
        //where('company_id', auth('api')->user()->company_id)

        $message = ReasonForNotVisiting::with(['visitor', 'customer', 'reson'])
            ->orderBy('created_at', 'desc')
            ->jsonPaginate($limt);
        return $message;
    }


    private function ISCompany()
    {
        if (auth('api')->user()['kind'] == 'admin')
            return 0;
        else
            return auth('api')->user()->company_id;
    }
}
