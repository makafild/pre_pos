<?php

namespace Core\Packages\product\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;
class StoreProductRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_fa' => 'required',
            "sublayer_id"=>[
                "int",
                'exists:constants,id,kind,' . Constant::SUBLAYER
            ],

            'per_master' => 'required|integer',
            'per_slave'  => 'required|integer',
            'pay_tax'  => 'required|boolean',

            'quotas_master' => 'required|integer',
            'quotas_slave'  => 'required|integer',
            'quotas_slave2' => 'required|integer',
            'min_quotas_master' => 'required|integer',
            'min_quotas_slave' => 'required|integer',
            'min_quotas_slave2' => 'required|integer',
            'order_column' => 'required|integer',

            'master_status' => 'integer|in:0,1',
            'slave_status'  => 'integer|in:0,1',
            'slave2_status' => 'integer|in:0,1',

            'master_unit.id' => 'required|exists:constants,id,kind,' . Constant::UNIT,
            'slave_unit.id'  => 'exists:constants,id,kind,' . Constant::UNIT,
            'slave2_unit.id' => 'exists:constants,id,kind,' . Constant::UNIT,

            'purchase_price' => 'integer',
            'sales_price'    => 'integer',
            'consumer_price' => 'integer',

            'brand.id'    => 'required|exists:brands,id',
            'category.id' => 'required|exists:categories,id',
            'photo_id'    => 'nullable|exists:files,id',

            'price_classes.*.price'    => 'required',
            'price_classes.*.id' => 'required',
            'photos'               => 'array',
            'photos.*.id'          => 'required|exists:files,id',
            'user_categories'      => 'array',
            'user_categories.*.id' => 'required|exists:constants,id,kind,' . Constant::CUSTOMER_CATEGORY,

            'barcodes'           => 'array',
            'barcodes.*.barcode' => 'required',
            'serial'=>'nullable',
            'length'=>'nullable|string',
            'width'=>'nullable|numeric|between:0,999999.99',
            'creator'=>'nullable',
            'number_of_page'=>'nullable|integer',
            'isbn'=>'nullable',
            'weight'=>'nullable|numeric|between:0,999999.99',
            'age_category'=>'nullable',
            'product_type_1' => 'nullable|array',
            'product_type_1.*' => [ Rule::exists('constants', 'id')
                ->where('kind',Constant::PRODUCT_TYPE_1)],
            'product_type_2' => ['nullable',  Rule::exists('constants', 'id')
                ->where('kind',Constant::PRODUCT_TYPE_2)]
        ];
    }
}
