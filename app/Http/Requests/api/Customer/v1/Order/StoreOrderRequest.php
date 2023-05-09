<?php

namespace App\Http\Requests\api\Customer\v1\Order;

use App\Models\Product\Product;
use App\Rules\Jalali;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreOrderRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Order
 *
 * @property string $description
 * @property string $payment_confirm
 * @property string $transfer_number
 * @property string $carriage_fares
 *
 * @property array $products
 * @property array $days
 * @property string $date_of_sending
 * @property array $coupons
 */
class StoreOrderRequest extends FormRequest
{
    /**
     * @var Product[]
     */
    private $productEntities;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{

		$rules = [
			'products'        => 'required|array',
			'products.*.id'   => [
				'required',
				Rule::exists('products', 'id')->where(function ($query) {
					return $query->where('status', Product::STATUS_AVAILABLE);
				}),
			],
			'date_of_sending' => [
				'required',
				new Jalali(),
			],
            'visitor_id' => [
                'nullable',
                'exists:users,id'
            ],
			'coupons'         => [
				'array',
				Rule::exists('coupons', 'coupon'),
			],
			'description'     => 'nullable',
			'payment_confirm' => 'nullable',
			'transfer_number' => 'nullable',
			'carriage_fares'  => 'nullable',
			'order_company_priorities'  => 'nullable|array',
			'order_company_priorities.*'  => 'nullable|numeric'
		];

        foreach ($this->products as $index => $product) {
            /** @var Product $productEntity */
            $productEntity = Product::where('id', $product['id'])
                ->with([
                    'MasterUnit',
                    'SlaveUnit',
                    'Slave2Unit',
                ])
                ->first();

            $this->productEntities[] = $productEntity;

            if (!$productEntity)
                continue;

            $rules["products.{$index}.master"] = [
                'required',
                'integer',
                'min:0',
                "max:" . ($productEntity->quotas_master ? $productEntity->quotas_master : 999),
            ];
            $rules["products.{$index}.slave"] = [
                'required',
                'integer',
                'min:0',
                "max:" . ($productEntity->quotas_slave ? $productEntity->quotas_slave : 999),
            ];
            $rules["products.{$index}.slave2"] = [
                'required',
                'integer',
                'min:0',
                "max:" . ($productEntity->quotas_slave2 ? $productEntity->quotas_slave2 : 999),
            ];
        }

        return $rules;

    }

    public function messages()
    {
        $messages = [];

        foreach ($this->products as $key => $product) {
            $productEntities = Product::find($product['id']);
            $messages["products.{$key}.id.exists"] = trans('validation.order_product_not_available', ['product' => $productEntities->name_fa]);

        }
        foreach ($this->productEntities as $key => $product) {
            if (isset($product->MasterUnit->constant_fa)) {

                $messages["products.{$key}.master.max"] = trans('validation.order_products_quotas_max', [
                    'unit' => $product->MasterUnit->constant_fa,
                    'name' => $product->name_fa,
                    'max' => $product->quotas_master,
                ]);
            }

            if (isset($product->SlaveUnit->constant_fa)) {

                $messages["products.{$key}.slave.max"] = trans('validation.order_products_quotas_max', [
                    'unit' => $product->SlaveUnit->constant_fa,
                    'name' => $product->name_fa,
                    'max' => $product->quotas_master,
                ]);
            }

            if (isset($product->Slave2Unit->constant_fa)) {
                $messages["products.{$key}.slave2.max"] = trans('validation.order_products_quotas_max', [
                    'unit' => $product->Slave2Unit->constant_fa,
                    'name' => $product->name_fa,
                    'max' => $product->quotas_master,
                ]);
            }
        }
        return $messages;
    }
}
