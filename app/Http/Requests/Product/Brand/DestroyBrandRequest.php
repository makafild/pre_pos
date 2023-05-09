<?php

namespace App\Http\Requests\Product\Brand;

use App\Rules\CheckBrandHasProduct;
use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DestroyBrandRequest
 *
 * @package App\Http\Requests\Product\Brand
 * @property array $brands
 */
class DestroyBrandRequest extends FormRequest
{
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
        return [
            'brands' => [
				'required',
				'array',
				new CheckRowVersion('brands'),
				new CheckBrandHasProduct(),
			]
        ];
    }
}
