<?php

namespace core\Packages\constant\src\controllers;

use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use Core\Packages\common\Constant;
use App\ModelFilters\ConstantFilter;
use const Siler\SwiftMailer\message;
use Core\System\Http\Controllers\CoreController;
use App\ModelFilters\listCategoryCustomerFilters;
use Core\Packages\constant\src\request\ListRequest;
use Core\Packages\constant\src\request\StoreRequest;
use Core\Packages\constant\src\request\UpdateRequest;
use Core\Packages\constant\src\request\CompanyRequest;
use Core\Packages\constant\src\request\DestroyRequest;
//use core\Packages\constant\src\filters\listCategoryCustomerFilters;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class ConstantPackageController extends CoreController
{
    private $_fillable = [
        'constant_en',
        'constant_fa',
        'kind',
        'company_id',
        'percent'
    ];

    public function index(Request $request)
    {

        $constants = Constant::where('company_id', auth('api')->user()->company_id)->filter($request->all());

        $datatable = datatables()->of($constants)
            // kind
            ->editColumn('kind', function (Constant $constant) {
                return trans("translate.setting.constant.{$constant->kind}");
            })
            ->filterColumn('kind', function ($query, $kind) {
                if ($kind == 'additions_deductions') {
                    $query->whereIn('kind', ['additions', 'deductions']);
                } elseif ($kind == 'product_type_1-product_type_2') {
                    $query->whereIn('kind', ['product_type_1', 'product_type_2']);
                } else {
                    $query->where('kind', $kind);
                }
            })
            // created_at
            ->editColumn('created_at', function (Constant $constant) {
                $v = new Verta($constant->created_at);

                return str_replace('-', '/', $v->formatDate());
            })
            ->filterColumn('created_at', function ($query, $date) {
                $date = Verta::parse($date)->DateTime();

                $query->whereDate('created_at', $date);
            })
            ->toJson();

        return $datatable;
    }

    public function listCategoryCustomer(Request $request ,Constant $const)
    {






    //old code
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }


        $constants = Constant::select('*');

        $constants = $constants->filter($request->all(), listCategoryCustomerFilters::class);

        $datatable = datatables()->of($constants)
            // kind
            ->editColumn('kind', function (Constant $constant) {
                return trans("translate.setting.constant.{$constant->kind}");
            })
            ->filterColumn('kind', function ($query, $kind) {
                $query->where('kind', 'customer_category');
            })
            // created_at
            ->editColumn('created_at', function (Constant $constant) {
                $v = new Verta($constant->created_at);

                return str_replace('-', '/', $v->formatDate());
            })
            ->filterColumn('created_at', function ($query, $date) {
                $date = Verta::parse($date)->DateTime();

                $query->whereDate('created_at', $date);
            });

            if ($request->has('sort')) {
                foreach ($sort_arr as $key => $value)
                    $cop = $datatable->get();
                // dd( $cop[0]->company);
                if ($cop[0]->$key == true) {
                    $datatable = $datatable->orderBy($key, $value);
                }

                if ($cop[0]->brand->$key != null) {

                    $datatable = $datatable->first()->company->orderBy($key, $value);

                }
            }

        return $datatable->toJson();
    }

    public function show($id)
    {
        $result = Constant::_()->list($id);
        return $this->responseHandler($result);
    }

    public function CategoryCustomer(StoreRequest $request)
    {
        $constant = new Constant();
        $constant->constant_fa = $request->constant_fa;
        $constant->constant_en = $request->constant_en;
        if ($request->has('percent')) {
            $constant->percent = $request->percent;
        }
        if (auth('api')->user()->company_id)
            $constant->company_id = auth('api')->user()->company_id;
        else
            $constant->company_id =    auth('api')->id();
        $constant->kind = 'customer_category';
        $constant->save();
        return [
            'status' => true,
            'message' => trans('messages.setting.constant.store'),
            'id' => $constant->id,
        ];
    }

    public function store(StoreRequest $request)
    {
        $constant = new Constant();
        $constant->constant_fa = $request->constant_fa;
        $constant->constant_en = $request->constant_en;
        if ($request->has('percent')) {
            $constant->percent = $request->percent;
        }
        if (auth('api')->user()->company_id)
            $constant->company_id = auth('api')->user()->company_id;
        else
            $constant->company_id =    auth('api')->id();

        $constant->kind = $request->kind;
        $constant->save();
        return [
            'status' => true,
            'message' => trans('messages.setting.constant.store'),
            'id' => $constant->id,
        ];
    }

    public function destroy(DestroyRequest $request , Constant $cons)
    {


        $cons->secureDelete($request->id , ['Additions','Details','DetailInvoices','Products_one','Products_many','Products_many_sec','Sliders','Users_many','Users']);

    }

    public function destroyCategory(DestroyRequest $request , Constant $cons)
    {

           $cons->secureDelete($request->id , ['Additions','Details','DetailInvoices','Products_one','Products_many','Products_many_sec','Sliders','Users_many','Users']);
    }

    public function update(UpdateRequest $request, $id)
    {
        $payload = $request->only($this->_fillable);
        $result = Constant::_()->where('kind', '!=', 'customer_category')->where('id', $id)->first();
        if ($request->constant_en)
            $result->constant_en = $payload['constant_en'];
        if ($request->constant_fa)
            $result->constant_fa = $payload['constant_fa'];
        if ($request->kind)
            $result->kind = $payload['kind'];
        if ($request->percent)
            $result->percent = $payload['percent'];
        $result->save();
        $result->save();
        return $this->responseHandler($result);
    }
    public function updateCategory(UpdateRequest $request, $id)
    {
        $payload = $request->only($this->_fillable);
        $result = Constant::_()->where('kind', 'customer_category')->where('id', $id)->first();
        if ($request->constant_en)
            $result->constant_en = $payload['constant_en'];
        if ($request->constant_fa)
            $result->constant_fa = $payload['constant_fa'];
        if ($request->kind)
            $result->kind = $payload['kind'];
        if ($request->percent)
            $result->percent = $payload['percent'];
        $result->save();
        return $this->responseHandler($result);
    }

    public function states()
    {
        $data = [];
        foreach (Constant::_()::CONSTANT_KINDS as $CONSTANT_KIND) {
            $data[] = [
                'name' => $CONSTANT_KIND,
                'title' => trans("translate.setting.constant.$CONSTANT_KIND"),
            ];
        };

        return response()->json(['kinds' => $data]);
    }

    public function list(ListRequest $request, $sort = "id", $order = "desc", $limit = 10)
    {

        $companyId = auth('api')->user()->company_id;
        $constants = Constant::filter($request->all(), ConstantFilter::class);

        if ($request->kind != 'customer_category') {

            $constants = $constants->where('company_id', $companyId);
        }

        $constants = $constants->where('kind', $request->kind)->orderBy('created_at', 'desc')->get();

        return $constants;
    }


    public function listConstantCompany(CompanyRequest $request)
    {

        if (auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') {
            $companyId = $request->company_id;
            $constants = Constant::filter($request->all(), ConstantFilter::class);
            $constants = $constants->where('company_id', $companyId);
            $constants = $constants->where('kind', $request->kind)->orderBy('created_at', 'desc')->get();
            return $constants;
        } else {
            return ["message" => trans("auth.permissation")];
        }
    }
}
