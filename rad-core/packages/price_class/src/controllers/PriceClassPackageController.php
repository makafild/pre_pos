<?php

namespace core\Packages\price_class\src\controllers;


use App\ModelFilters\ConstantFilter;
use App\ModelFilters\PriceClassFilter;
use Core\Packages\common\Constant;
use Core\Packages\price_class\src\request\DestroyRequest;
use Core\Packages\price_class\src\request\ListRequest;
use Core\Packages\price_class\src\request\StoreRequest;
use Core\Packages\price_class\src\request\UpdateRequest;
use Core\Packages\price_class\PriceClass;
use Core\System\Http\Controllers\CoreController;
use Illuminate\Http\Request;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class PriceClassPackageController extends CoreController
{
    private $_fillable = [
        'title',
    ];

    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        $companyId = auth('api')->user()->company_id;

        $priceClass = PriceClass::with('company');
        if ($companyId) {
            $priceClass = $priceClass->where('price_classes.company_id', $companyId);
        }

        $priceClass = $priceClass->filter($request->all(), PriceClassFilter::class)->orderBy($sort, $order)->jsonPaginate($limit);

        if ($request->has('filter') && $request->get('filter') == 'false') {
            $priceClass = PriceClass::latest()->get();
        }

        return $priceClass;
    }

    public function show($id)
    {

        $result = PriceClass::_()->list($id);
        return $this->responseHandler($result);
    }

    public function store(StoreRequest $request)
    {
        $priceClass = new PriceClass();
        $priceClass->title = $request->title;
        $priceClass->company_id = auth('api')->user()->company_id;
        $priceClass->save();

        return [
            'status' => true,
            'message' => trans('messages.user.price_class.store'),
            'id' => $priceClass->id,
        ];
    }

    public function destroy(DestroyRequest $request)
    {
        $result = PriceClass::_()->destroyRecord($request->id);
        return [
            'status' => true,
            'message' => trans('messages.user.price_class.delete'),
        ];
    }

    public function update(UpdateRequest $request, $id)
    {
        $payload = $request->only($this->_fillable);
        $result = PriceClass::_()->updateRow($payload, $id);
        return [
            'status' => true,
            'message' => trans('messages.user.price_class.update'),
        ];
    }

    public function states()
    {
        $data = [];
        foreach (PriceClass::_()::CONSTANT_KINDS as $CONSTANT_KIND) {
            $data[] = [
                'name' => $CONSTANT_KIND,
                'title' => trans("translate.setting.constant.$CONSTANT_KIND"),
            ];
        };

        return response()->json(['kinds' => $data]);
    }

    public function list(ListRequest $request)
    {
        $companyId = auth('api')->user()->company_id;

        $priceClasses = PriceClass::with('company')->CompanyId($companyId)
            ->latest()
            ->get();

        return $priceClasses;
    }

}
